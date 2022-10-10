<?php

declare(strict_types=1);

namespace AmsterdamPHP\Console\Unit\Command;

use AmsterdamPHP\Console\Api\JoindInClient;
use AmsterdamPHP\Console\Api\MeetupClient;
use AmsterdamPHP\Console\Api\Middleware\JsonAwareResponse;
use AmsterdamPHP\Console\Api\SlackWebhookClient;
use AmsterdamPHP\Console\Command\CreateJoindInCommand;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Ramsey\Collection\Collection;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

use function is_array;

class CreateJoindInCommandTest extends MockeryTestCase
{
    protected MeetupClient&MockInterface $meetup;
    protected SlackWebhookClient&MockInterface $slack;
    protected JoindInClient&MockInterface $joindin;

    /** @throws ExceptionInterface */
    public function testCommand(): void
    {
        $this->meetup->expects('getUpcomingEventsForGroup')
                     ->withArgs(['amsterdamphp'])
                     ->andReturns(new Collection('array', [
                         [
                             'name' => 'Monthly Meeting',
                             'time' => 1665252181 * 1000, //October 8 2022
                             'event_url' => 'https://meetup.link',
                             'venue' => ['name' => 'HQ'],
                         ],
                     ]));

        $this->joindin->expects('submitEvent')
            ->withArgs(static fn ($event) => $event['name'] === 'AmsterdamPHP Monthly Meeting - October/2022')
            ->andReturns('34')
            ->once();

        $this->joindin->expects('addEventHost')
            ->withArgs(['34', 'amsterdamphp'])
            ->andReturns(new JsonAwareResponse(200))
            ->once();

        $this->slack->expects('sendMessage')
            ->withArgs(static fn ($args) => is_array($args))
            ->once();

        $input  = new ArrayInput([]);
        $output = new NullOutput();

        $this->command->run($input, $output);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->meetup  = Mockery::mock(MeetupClient::class);
        $this->slack   = Mockery::mock(SlackWebhookClient::class);
        $this->joindin = Mockery::mock(JoindInClient::class);

        $this->command = new CreateJoindInCommand($this->meetup, $this->slack, $this->joindin);
    }
}
