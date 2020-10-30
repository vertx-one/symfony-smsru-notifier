<?php

namespace Vertx666\Symfony\Component\Notifier\Bridge\Smsru;

use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SmsruTransport extends AbstractTransport
{
    protected const HOST = 'sms.ru';

    /** @var array */
    private $auth;
    /** @var string|null */
    private $from;

    public function __construct(array $auth, ?string $from = null, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->auth = $auth;
        $this->from = $from;

        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return sprintf('smsru://%s?from=%s', $this->getEndpoint(), $this->from);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    protected function doSend(MessageInterface $message): void
    {
        if (!$message instanceof SmsMessage) {
            throw new LogicException(sprintf('The "%s" transport only supports instances of "%s" (instance of "%s" given).', __CLASS__, SmsMessage::class, get_debug_type($message)));
        }

        $endpoint = sprintf('https://%s/sms/send', $this->getEndpoint());
        $body = array_merge(
            $this->auth,
            [
                "to" => $message->getPhone(),
                "msg" => $message->getSubject(),
                "json" => 1,
            ]
        );
        $response = $this->client->request('POST', $endpoint, [
            'body' => $body,
        ]);


        if ($response->getStatusCode() !== \Symfony\Component\HttpFoundation\Response::HTTP_OK) {
            throw new TransportException('Unable to send the SMS', $response);
        }

        $body = $response->toArray(false);

        if ($body['status_code'] !== 100) {
            throw new TransportException('Unable to send the SMS', $response);
        }
    }
}
