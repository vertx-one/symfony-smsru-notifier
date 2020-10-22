<?php

namespace Vertx666\Symfony\Component\Notifier\Bridge\Smsru;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

final class SmsruTransportFactory extends AbstractTransportFactory
{
    /**
     * @return SmsruTransport
     */
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();
        $accountSid = $this->getUser($dsn);
        $authToken = $this->getPassword($dsn);
        $from = $dsn->getOption('from');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        if ('smsru' === $scheme) {
            return (new SmsruTransport($accountSid, $authToken, $from, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
        }

        throw new UnsupportedSchemeException($dsn, 'smsru', $this->getSupportedSchemes());
    }

    protected function getSupportedSchemes(): array
    {
        return ['smsru'];
    }
}
