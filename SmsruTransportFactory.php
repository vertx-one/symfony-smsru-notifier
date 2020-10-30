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

        if ('smsru' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'smsru', $this->getSupportedSchemes());
        }

        $api_id = $dsn->getOption('api_id');

        if($api_id) {
            $auth = [
                'api_id' => $api_id
            ];
        } else {
            $auth = [
                'login' => $this->getUser($dsn),
                'password' => $this->getPassword($dsn),
            ];
        }

        $from = $dsn->getOption('from');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new SmsruTransport($auth, $from, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['smsru'];
    }
}