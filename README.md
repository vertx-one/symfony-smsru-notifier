# symfony-smsru-notifier

Транспорт через sms.ru для компонента Symfony Notifier

Добавляем в .env
```ini
SMSRU_DSN=smsru://login:password@default?from=AUTHOR
# или
SMSRU_DSN=smsru://default?api_id=API_ID&from=AUTHOR
```

Добавляем в конфиг
```yaml
framework:
    notifier:
        texter_transports:
            smsru: '%env(SMSRU_DSN)%'
```


```yaml
services:
    Vertx666\Symfony\Component\Notifier\Bridge\Smsru\SmsruTransportFactory:
        tags: [ texter.transport_factory ]
```