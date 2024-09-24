<?php declare(strict_types=1);

namespace Vconnect\DelayedAmqp\MessageQueue\Topology\Config\Xml;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Exception\NotFoundException;

class SchemaLocator extends \Magento\Framework\MessageQueue\Topology\Config\Xml\SchemaLocator
{
    /**
     * Initialize dependencies.
     *
     * @param UrnResolver $urnResolver
     * @param string $mergedSchemaUrn
     * @param string $schemaUrn
     * @throws NotFoundException
     */
    public function __construct(
        UrnResolver $urnResolver,
        string $mergedSchemaUrn,
        string $schemaUrn
    ) {
        parent::__construct($urnResolver);
        $this->schema = $urnResolver->getRealPath($mergedSchemaUrn);
        $this->perFileSchema = $urnResolver->getRealPath($schemaUrn);
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema(): ?string
    {
        return $this->perFileSchema;
    }
}
