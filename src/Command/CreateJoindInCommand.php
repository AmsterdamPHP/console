<?php

declare(strict_types=1);

namespace AmsterdamPHP\Console\Command;

use AmsterdamPHP\Console\Api\JoindInClient;
use AmsterdamPHP\Console\Api\MeetupClient;
use AmsterdamPHP\Console\Api\SlackWebhookClient;
use Codeliner\ArrayReader\ArrayReader;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use IntlDateFormatter;
use JsonException;
use Ramsey\Collection\CollectionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;
use function str_contains;
use function urlencode;

class CreateJoindInCommand extends Command
{
    public function __construct(
        private readonly MeetupClient $meetup,
        private readonly SlackWebhookClient $slack,
        private readonly JoindInClient $joindinApi,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('monthly:create-placeholder')
            ->setDescription('Creates the monthly meeting event at joind.in');
    }

    /**
     * @throws JsonException
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $meetingCandidates = $this->getCurrentMonthlyMeetingCandidates();

        if ($meetingCandidates->count() > 1) {
            $this->sendSlackMsg('Too many monthly meetings found, I was confused, sorry.', 'construction');
            $output->writeln('<error>Too many monthly meetings</error>');

            return 0;
        }

        $meeting = new ArrayReader($meetingCandidates->first());

        $time = $meeting->integerValue('time') / 1000;
        $date = DateTime::createFromFormat('U', (string) $time);

        $output->writeln(
            sprintf(
                '<comment>=> Current meeting found: </comment><info>%s</info>',
                $meeting->stringValue('name'),
            ),
        );

        $event = [
            'name'         => sprintf('AmsterdamPHP Monthly Meeting - %s', IntlDateFormatter::formatObject($date, 'MMMM/y')),
            'description'  => 'Every month AmsterdamPHP holds a monthly meeting with a speaker a social event. You can find more info and signup at http://meetup.amsterdamphp.nl',
            'start_date'   => $date->format('Y-m-d'),
            'end_date'     => $date->format('Y-m-d'),
            'tz_continent' => 'Europe',
            'tz_place'     => 'Amsterdam',
            'href'         => $meeting->stringValue('event_url'),
            'location'     => $meeting->stringValue('venue.name'),
            'tags'         => 'php, amsterdam',
        ];

        $eventId = $this->joindinApi->submitEvent($event);
        $output->writeln(sprintf('<comment>=> Joind.in event created: %s</comment>', $eventId));

        $hostResult = $this->joindinApi->addEventHost($eventId, 'amsterdamphp');
        $output->writeln(sprintf('<comment>=> Host Add request returned %s</comment>', $hostResult->getStatusCode()));

        $this->sendSlackMsg(
            sprintf(
                'Joind.in event created successfully, its awaiting approval. Find it here: https://joind.in/search?keyword=%s',
                urlencode($event['name']),
            ),
        );

        $output->writeln('<comment>=> Payload sent to Slack.</comment>');

        return 0;
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    protected function sendSlackMsg(string $msg, string $icon = 'date'): void
    {
        $message = [
            'channel' => '#monthly-meetings',
            'text' => $msg,
            'username' => 'AmsterdamPHP Console: joind.in',
            'icon_emoji' => $icon,
        ];

        $this->slack->sendMessage($message);
    }

    /**
     * Finds possible candidates for this month's meeting.
     * Should only return one result.
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    protected function getCurrentMonthlyMeetingCandidates(): CollectionInterface
    {
        return $this->meetup->getUpcomingEventsForGroup('amsterdamphp')->filter(static function ($event) {
            return str_contains($event['name'], 'Monthly Meeting');
        });
    }
}
