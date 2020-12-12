<?php

namespace App\Adapter\DataTransformer\Validator;

/**
 * Class ObjectPropertiesValidator
 * @package App\Adapter\DataTransformer\Validator
 */
class ObjectPropertiesValidator
{
    private array $missedProperties = [];

    /**
     * @param object $input
     * @param array $requiredProperties
     * @return bool
     */
    public function isValidObject(object $input, array $requiredProperties): bool
    {
        foreach ($requiredProperties as $propertyName) {
            if (!property_exists($input, $propertyName)) {
                $this->missedProperties[] = $propertyName;
            }
        }

        return empty($this->missedProperties);
    }

    /**
     * @return string
     */
    public function getJoinedMissesProperties(): string
    {
        return join(', ', $this->missedProperties);
    }
}
