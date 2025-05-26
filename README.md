# vConnect Delayed AMQP

## Purpose
This extension enhances the Magento Message Queue framework to make it compatible with the [delayed message exchange plugin for RabbitMQ](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/).

## Installation
To install the extension, run the following command:
```bash
composer require vconnect/module-delayed-amqp
bin/magento setup:upgrade
```

## Usage
1. Message Queue configurations

Configurations for communication.xml and queue_consumer.xml remain unchanged. Delayed messaging configurations are defined in queue_topology.xml

Example of queue_topology.xml
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:vconnect:module:Vconnect_DelayedAmqp:etc/topology.xsd">
    <exchange name="magento-delayed" type="x-delayed-message" connection="amqp">
        <binding id="processDelayedMessage"
                 topic="vconnect.delayed.example"
                 destinationType="queue"
                 destination="vconnect.delayed.example">
        </binding>
        <arguments>
            <argument name="x-delayed-type" xsi:type="string">topic</argument>
        </arguments>
    </exchange>
</config>
```

Note: the exchange of type 'x-delayed-message' should have a unique name to avoid conflict with an exchange of the default type 'topic';

Additionally, exchange name should match the configurations in queue_publisher.xml

Example of queue_publisher.xml
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
    <publisher topic="vconnect.delayed.example">
        <connection name="amqp" exchange="magento-delayed" disabled="false" />
    </publisher>
</config>
```

Please use `\Vconnect\DelayedAmqp\MessageQueue\DelayedPublisher` to publish a delayed message (default is 5000 ms delay).

```php
class EventDispatcher
{
    public function __construct(
        private readonly DelayedPublisher $delayedPublisher
    ) {
    }

   public function execute(array $data)
    {
        $this->delayedPublisher->publish(
            'vconnect.delayed.example',
            $data,
            10000
        );
    }
}
```

## Example

You can find an example of working extension [here](https://github.com/vConnect-dk/delayed-amqp-example).
