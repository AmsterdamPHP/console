<?php
namespace AmsterdamPHP\Console;

use AmsterdamPHP\Console\Api\JoindInClient;
use AmsterdamPHP\Console\Command\CreateJoindInCommand;
use Crummy\Phlack\Builder\MessageBuilder;
use Crummy\Phlack\Message\Message;
use Crummy\Phlack\Phlack;
use DMS\Service\Meetup\AbstractMeetupClient;
use DMS\Service\Meetup\Response\MultiResultResponse;
use Guzzle\Http\Message\Header;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Adapter\MockAdapter;
use GuzzleHttp\Event\Emitter;
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

    /**
     * @var MockAdapter
     */
    protected $adapter;

    /**
     * @var JoindInClient
     */
    protected $joindinClient;

    protected function setUp()
    {
        parent::setUp();
        $this->meetup = \Mockery::mock(AbstractMeetupClient::class);
        $this->slack  = \Mockery::mock(Phlack::class);
        $this->joindinEvents = \Mockery::mock(Client::class);
        $this->joindinEvents->shouldReceive('getService')->andReturnSelf();
        $this->adapter         = new MockAdapter();
        $this->joindinClient = new JoindInClient([
            'adapter' => $this->adapter,
            'emitter' => new Emitter()
                                                 ]);

        $this->command = new CreateJoindInCommand($this->meetup, $this->slack, $this->joindinEvents, $this->joindinClient);
    }

    public function testCommand()
    {
        $meetupResponse = \Mockery::mock(MultiResultResponse::class)->shouldDeferMissing();
        $meetupResponse->addHeader('Content-Type', new Header('Content-Type', 'application/json'));
        $meetupResponse->setData([
            [
                'name' => 'Monthly Meeting',
                'time' => time() * 1000,
                'event_url' => 'http://meetup.link',
                'venue' => ['name' => 'HQ'],
            ]
        ]);

        $this->meetup->shouldReceive('getEvents')->andReturn($meetupResponse)->once();

        $this->joindinEvents->shouldReceive('submit')
                            ->andReturn(['url' => 'http://some.path.to.api/v2.1/events/34'])
                            ->once();

        $this->slack->shouldReceive('getMessageBuilder')->andReturn(new MessageBuilder())->once();
        $this->slack->shouldReceive('send')->with(
            \Mockery::on(
                function (Message $param) {
                    $this->assertContains('event created', $param->get('text'));
                    return true;
                }
            )
        );

        $this->adapter->setResponse(new Response(200));

        $input = new ArrayInput([]);
        $output = new DummyOutput();

        $this->command->run($input, $output);
    }

}
