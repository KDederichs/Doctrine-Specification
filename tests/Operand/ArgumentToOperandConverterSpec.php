<?php

namespace tests\Happyr\DoctrineSpecification\Operand;

use Happyr\DoctrineSpecification\Operand\ArgumentToOperandConverter;
use Happyr\DoctrineSpecification\Operand\Field;
use Happyr\DoctrineSpecification\Operand\Operand;
use Happyr\DoctrineSpecification\Operand\Value;
use PhpSpec\ObjectBehavior;

/**
 * @mixin ArgumentToOperandConverter
 */
class ArgumentToOperandConverterSpec extends ObjectBehavior
{
    public function it_is_a_converter()
    {
        $this->shouldBeAnInstanceOf('Happyr\DoctrineSpecification\Operand\ArgumentToOperandConverter');
    }

    public function it_not_convert_operand_to_field(Operand $operand)
    {
        $this->toField($operand)->shouldReturn($operand);
    }

    public function it_convert_argument_to_field()
    {
        $this->toField('foo')->shouldBeAnInstanceOf('Happyr\DoctrineSpecification\Operand\Field');
    }

    public function it_not_convert_operand_to_value(Operand $operand)
    {
        $this->toValue($operand)->shouldReturn($operand);
    }

    public function it_convert_argument_to_value()
    {
        $this->toValue('foo')->shouldBeAnInstanceOf('Happyr\DoctrineSpecification\Operand\Value');
    }

    public function it_is_all_arguments_a_operands(Operand $first, Operand $second)
    {
        $arguments = array($first, $second);
        $this->isAllOperands($arguments)->shouldReturn(true);
    }

    public function it_is_not_all_arguments_a_operands(Operand $first, Operand $second)
    {
        $arguments = array($first, 'foo', $second);
        $this->isAllOperands($arguments)->shouldReturn(false);
    }

    public function it_no_nothing_to_convert()
    {
        $this->convert(array())->shouldReturn([]);
    }

    public function it_a_convertible_field()
    {
        $subject = $this->convert(array('foo'));
        $subject->shouldBeArray();
        $subject->shouldHaveCount(1);
        $subject->shouldHaveField();
    }

    public function it_a_already_converted_field(Operand $field)
    {
        $this->convert(array($field))->shouldReturn(array($field));
    }

    public function it_a_convertible_field_and_value()
    {
        $subject = $this->convert(array('foo', 'bar'));
        $subject->shouldBeArray();
        $subject->shouldHaveCount(2);
        $subject->shouldHaveField();
        $subject->shouldHaveValue();
    }

    public function it_a_already_converted_value(Operand $field, Operand $value)
    {
        $this->convert(array($field, $value))->shouldReturn(array($field, $value));
    }

    public function it_a_already_converted_value2(Operand $value)
    {
        $subject = $this->convert(array('foo', $value));
        $subject->shouldBeArray();
        $subject->shouldHaveCount(2);
        $subject->shouldHaveField();
        $subject->shouldHaveOperandAt(1);
    }

    public function it_a_convertible_arguments(Operand $first, Operand $second)
    {
        $subject = $this->convert(array('foo', $first, $second, 'bar'));
        $subject->shouldBeArray();
        $subject->shouldHaveCount(4);
        $subject->shouldHaveField();
        $subject->shouldHaveValue();
        $subject->shouldHaveOperandAt(1);
        $subject->shouldHaveOperandAt(2);
    }

    public function it_is_not_convertible_arguments(Field $field, Operand $operand, Value $value)
    {
        $this->shouldThrow('Happyr\DoctrineSpecification\Exception\NotConvertibleException')
            ->duringConvert(array($field, $operand, 'foo', $value));
    }

    public function getMatchers()
    {
        return array(
            'haveField' => function ($subject) {
                return $subject[0] instanceof Field;
            },
            'haveValue' => function ($subject) {
                return $subject[count($subject) - 1] instanceof Value;
            },
            'haveOperandAt' => function ($subject, $position) {
                return $subject[$position] instanceof Operand;
            },
        );
    }
}
