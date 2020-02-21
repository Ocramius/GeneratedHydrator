<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\CodeGenerator\Visitor;

use GeneratedHydrator\CodeGenerator\Visitor\ObjectProperty;
use GeneratedHydratorTestAsset\ClassWithTypedProperties;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/** @covers \GeneratedHydrator\CodeGenerator\Visitor\ObjectProperty */
class ObjectPropertyTest extends TestCase
{
    public function testObjectPropertyState() : void
    {
        $property0 = ObjectProperty::fromReflection(new ReflectionProperty(ClassWithTypedProperties::class, 'property0'));
        $property1 = ObjectProperty::fromReflection(new ReflectionProperty(ClassWithTypedProperties::class, 'property1'));
        $property2 = ObjectProperty::fromReflection(new ReflectionProperty(ClassWithTypedProperties::class, 'property2'));
        $property3 = ObjectProperty::fromReflection(new ReflectionProperty(ClassWithTypedProperties::class, 'property3'));
        $property4 = ObjectProperty::fromReflection(new ReflectionProperty(ClassWithTypedProperties::class, 'property4'));
        $untyped0  = ObjectProperty::fromReflection(new ReflectionProperty(ClassWithTypedProperties::class, 'untyped0'));
        $untyped1  = ObjectProperty::fromReflection(new ReflectionProperty(ClassWithTypedProperties::class, 'untyped1'));

        self::assertSame('property0', $property0->name);
        self::assertSame('property1', $property1->name);
        self::assertSame('property2', $property2->name);
        self::assertSame('property3', $property3->name);
        self::assertSame('property4', $property4->name);
        self::assertSame('untyped0', $untyped0->name);
        self::assertSame('untyped1', $untyped1->name);

        self::assertTrue($property0->hasType);
        self::assertTrue($property1->hasType);
        self::assertTrue($property2->hasType);
        self::assertTrue($property3->hasType);
        self::assertTrue($property4->hasType);
        self::assertFalse($untyped0->hasType);
        self::assertFalse($untyped1->hasType);

        self::assertTrue($property0->hasDefault);
        self::assertTrue($property1->hasDefault);
        self::assertFalse($property2->hasDefault);
        self::assertFalse($property3->hasDefault);
        self::assertTrue($property4->hasDefault);
        self::assertTrue($untyped0->hasDefault);
        self::assertTrue($untyped1->hasDefault);

        self::assertFalse($property0->allowsNull);
        self::assertTrue($property1->allowsNull);
        self::assertFalse($property2->allowsNull);
        self::assertTrue($property3->allowsNull);
        self::assertTrue($property4->allowsNull);
        self::assertTrue($untyped0->allowsNull);
        self::assertTrue($untyped1->allowsNull);
    }
}
