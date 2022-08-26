<?php

namespace AmsterdamPHP\Console\Command;

use AmsterdamPHP\Console\Api\JoindInClient;
use Codeliner\ArrayReader\ArrayReader;
use Crummy\Phlack\Phlack;
use DMS\Service\Meetup\AbstractMeetupClient;
use Joindin\Api\Client;
use Joindin\Api\Description\Events;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function var_dump;

class CreateJoindInCommand extends Command
{

    /**
     * @var AbstractMeetupClient
     */
    protected $meetup;

    /**
     * @var Phlack
     */
    protected $slack;

    /**
     * @var Client
     */
    protected $joindinEvents;

    /**
     * @var JoindInClient
     */
    private $joindinApi;

    /**
     * CreateJoindInCommand constructor.
     *
     * @param AbstractMeetupClient $meetup
     * @param Phlack               $slack
     * @param Client               $joindin
     * @param JoindInClient        $joindinApi
     */
    public function __construct(AbstractMeetupClient $meetup, Phlack $slack, Client $joindin, JoindInClient $joindinApi)
    {
        $this->meetup = $meetup;
        $this->slack = $slack;
        $this->joindinEvents = $joindin->getService(new Events());
        $this->joindinApi = $joindinApi;
        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('monthly:create-placeholder')
            ->setDescription('Creates the monthly meeting event at joind.in');
    }

    /**
     * @see Command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $meetingCandidates = $this->getCurrentMonthlyMeetingCandidates();

        if ($meetingCandidates->count() > 1) {
            $this->sendSlackMsg('Too many monthly meetings found, I was confused, sorry.', 'construction');
            $output->writeln("<error>Too many monthly meetings</error>");
            return;
        }

        $meeting = new ArrayReader($meetingCandidates->current());

        $time = $meeting->integerValue('time') / 1000;
        $date = \DateTime::createFromFormat('U', $time);

        $output->writeln(
            sprintf(
                "<comment>=> Current meeting found: </comment><info>%s</info>",
                $meeting->stringValue('name')
            )
        );

        $event = [
            'name'         => sprintf('AmsterdamPHP Monthly Meeting - %s', strftime('%B/%Y', $date->format('U'))),
            'description'  => 'Every month AmsterdamPHP holds a monthly meeting with a speaker a social event. You can find more info and signup at http://meetup.amsterdamphp.nl',
            'start_date'   => $date->format('Y-m-d'),
            'end_date'     => $date->format('Y-m-d'),
            'tz_continent' => 'Europe',
            'tz_place'     => 'Amsterdam',
            'href'         => $meeting->stringValue('event_url'),
            'location'     => $meeting->stringValue('venue.name'),
            'tags'         => 'php, amsterdam'
        ];

        $result = $this->joindinEvents->submit($event);
        $output->writeln(sprintf("<comment>=> Joind.in event created.</comment>"));

        $hostResult = $this->joindinApi->addEventHost($this->extractIdFromLocation($result['url']), 'amsterdamphp');
        $output->writeln("<comment>=> Host Add request returned " . $hostResult->getStatusCode() . "</comment>");

        $this->sendSlackMsg(
            sprintf(
                'Joind.in event created successfully, its awaiting approval. Find it here: https://joind.in/search?keyword=%s',
                urlencode($event['name'])
            )
        );

        $output->writeln("<comment>=> Payload sent to Slack.</comment>");
    }

    /**
     * Sends a message to Slack
     *
     * @param string $msg
     * @param string $icon
     */
    protected function sendSlackMsg($msg, $icon = 'date')
    {

        $builder = $this->slack->getMessageBuilder();

        $builder->setChannel('#monthly-meetings');
        $builder->setText($msg);
        $builder->setUsername('AmsterdamPHP Console: joind.in');
        $builder->setIconEmoji($icon);

        $this->slack->send($builder->create());
    }

    /**
     * Finds possible candidates for this month's meeting.
     * Should only return one result.
     *
     * @return \DMS\Service\Meetup\Response\MultiResultResponse
     */
    protected function getCurrentMonthlyMeetingCandidates()
    {
        //Get Upcoming events
        $events = $this->meetup->getEvents(
            [
                'group_urlname' => 'amsterdamphp',
                'status'        => 'upcoming',
                'text_format'   => 'plain',
                'time'          => '0m,1m'
            ]
        );

        return $events->filter(function($event) {
            return strpos($event['name'], 'Monthly Meeting') !== false;
        });
    }

    protected function extractIdFromLocation($locationUrl)
    {
        $matches = [];
        preg_match(
            "/events\/([0-9]*)/",
            $locationUrl,
            $matches
        );

        return $matches[1];
    }
}
