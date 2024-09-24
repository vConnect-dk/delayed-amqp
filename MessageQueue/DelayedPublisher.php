<?php declare(strict_types=1);

namespace Vconnect\DelayedAmqp\MessageQueue;

use Magento\Framework\Amqp\Config as AmqpConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\ExchangeRepository;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * A MessageQueue Publisher to handle publishing a message.
 */
class DelayedPublisher implements PublisherInterface
{
    public function __construct(
        private readonly ExchangeRepository $exchangeRepository,
        private readonly EnvelopeFactory $envelopeFactory,
        private readonly MessageEncoder $messageEncoder,
        private readonly MessageValidator $messageValidator,
        private readonly PublisherConfig $publisherConfig,
        private readonly AmqpConfig $amqpConfig
    ) {
    }

    /**
     * @param string $topicName
     * @param mixed $data
     * @param int $delay
     * @return null
     * @throws LocalizedException
     */
    public function publish($topicName, $data, int $delay = 5000)
    {
        $this->messageValidator->validate($topicName, $data);
        $data = $this->messageEncoder->encode($topicName, $data);
        $envelope = $this->envelopeFactory->create(
            [
                'body' => $data,
                'properties' => [
                    'delivery_mode' => 2,
                    // md5() here is not for cryptographic use.
                    // phpcs:ignore Magento2.Security.InsecureFunction
                    'message_id' => md5(gethostname() . microtime(true) . uniqid($topicName, true)),
                    'application_headers' => [
                        'x-delay' => ['u', $delay]
                    ]
                ]
            ]
        );
        $connectionName = $this->publisherConfig->getPublisher($topicName)->getConnection()->getName();
        $connectionName = ($connectionName === 'amqp' && !$this->isAmqpConfigured()) ? 'db' : $connectionName;
        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);
        $exchange->enqueue($topicName, $envelope);
        return null;
    }

    /**
     * Check Amqp is configured.
     *
     * @return bool
     */
    private function isAmqpConfigured(): bool
    {
        return (bool)$this->amqpConfig->getValue(AmqpConfig::HOST);
    }
}
