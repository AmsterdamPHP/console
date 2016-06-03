<?php
namespace AmsterdamPHP\Console;

use AmsterdamPHP\Console\Command\CreateJoindInCommand;
use Crummy\Phlack\Builder\MessageBuilder;
use Crummy\Phlack\Message\Message;
use Crummy\Phlack\Phlack;
use DMS\Service\Meetup\AbstractMeetupClient;
use DMS\Service\Meetup\Response\MultiResultResponse;
use Joindin\Api\Client;
use Mockery\Mock;
use Mockery\MockInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Tests\Fixtures\DummyOutput;

class CreateJoindInCommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AbstractMeetupClient | MockInterface
     */
    protected $meetup;

    /**
     * @var Phlack | MockInterface
     */
    protected $slack;

    /**
     * @var Client | MockInterface
     */
    protected $joindinEvents;

    /**
     * @var CreateJoindInCommand
     */
    protected $command;

    protected function setUp()
    {
        parent::setUp();
        $this->meetup = \Mockery::mock(AbstractMeetupClient::class);
        $this->slack  = \Mockery::mock(Phlack::class);
        $this->joindinEvents = \Mockery::mock(Client::class);
        $this->joindinEvents->shouldReceive('getService')->andReturnSelf();

        $this->command = new CreateJoindInCommand($this->meetup, $this->slack, $this->joindinEvents);
    }

    public function testCommand()
    {
        $meetupResponse = \Mockery::mock(MultiResultResponse::class)->shouldDeferMissing();
        $meetupResponse->setData([
            [
                'name' => 'Monthly Meeting',
                'time' => time() * 1000,
                'event_url' => 'http://meetup.link',
                'venue' => ['name' => 'HQ'],
            ]
        ]);

        $this->meetup->shouldReceive('getEvents')->andReturn($meetupResponse)->once();

        $this->joindinEvents->shouldReceive('submit')->once();

        $this->slack->shouldReceive('getMessageBuilder')->andReturn(new MessageBuilder())->once();
        $this->slack->shouldReceive('send')->with(
            \Mockery::on(
                function (Message $param) {
                    $this->assertContains('event created', $param->get('text'));
                    return true;
                }
            )
        );

        $input = new ArrayInput([]);
        $output = new DummyOutput();

        $this->command->run($input, $output);
    }

}
