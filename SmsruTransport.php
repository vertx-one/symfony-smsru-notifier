<?php

namespace Vertx666\Symfony\Component\Notifier\Bridge\Smsru

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

    private $apiId;
    private $from;

    public function __construct(string $api_id, string $from, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->apiId = $api_id;
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
        $response = $this->client->request('POST', $endpoint, [
            'body' => [
                "api_id" => $this->apiId,
                "to" => $message->getPhone(),
                "msg" => $message->getSubject(),
                "json" => 1,
            ],
        ]);


        if ($response->getStatusCode() !== \Symfony\Component\HttpFoundation\Response::HTTP_OK) {
            throw new TransportException('Unable to send the SMS', $response);
        }

        $body = $response->toArray(false);

        if($body['status_code'] !== 100) {
            throw new TransportException('Unable to send the SMS', $response);
        }
    }
}
