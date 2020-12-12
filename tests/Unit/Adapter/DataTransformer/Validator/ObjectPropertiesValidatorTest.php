<?php

namespace App\Test\Unit\Adapter\DataTransformer\Validator;


use App\Adapter\DataTransformer\Validator\ObjectPropertiesValidator;
use PHPUnit\Framework\TestCase;

class ObjectPropertiesValidatorTest extends TestCase
{
    /**
     * @dataProvider getTestMissedPropertiesValidationData
     * @param array $input
     * @param array $requiredProperties
     * @param bool $expecedIsValid
     * @param string $exceptedJoinedMissedProperties
     */
    public function testMissedPropertiesValidation(
        array $input,
        array $requiredProperties,
        bool $expecedIsValid,
        string $exceptedJoinedMissedProperties
    ) {

        $inputObj = (object) $input;

        $objectPropertiesValidator = new ObjectPropertiesValidator();

        $this->assertEquals(
            $expecedIsValid,
            $objectPropertiesValidator->isValidObject($inputObj, $requiredProperties)
        );

        $this->assertEquals(
            $exceptedJoinedMissedProperties,
            $objectPropertiesValidator->getJoinedMissesProperties()
        );
    }

    /**
     * @return array[]
     */
    public function getTestMissedPropertiesValidationData(): array
    {
        return [
            'no data' => [
                [],
                ['title', 'img'],
                false,
                'title, img',
            ],
            'only image' => [
                [
                    'img' => 'my img',
                ],
                ['title', 'img'],
                false,
                'title',
            ],
            'valid' => [
                [
                    'img' => 'my img',
                    'title' => 'my title',
                ],
                ['title', 'img'],
                true,
                '',
            ],
        ];
    }
}
