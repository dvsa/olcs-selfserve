<?php

declare(strict_types=1);

namespace Olcs\InputFilter;

use Common\Form\InputFilter;
use Laminas\Filter\StringToUpper;
use Laminas\InputFilter\Input;
use Laminas\Validator\InArray;
use Olcs\Controller\Licence\Vehicle\ListVehicleController;
use Olcs\Mvc\Strategy\Validation\ValidationException;

class ListVehicleIndexActionInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add($this->newSortColumnInput(ListVehicleController::QUERY_KEY_SORT_REMOVED_VEHICLES, ['v.vrm', 'specifiedDate', 'removalDate']));
        $this->add($this->newSortColumnInput(ListVehicleController::QUERY_KEY_SORT_CURRENT_VEHICLES, ['v.vrm', 'specifiedDate']));
        $this->add($this->newOrderInput(ListVehicleController::QUERY_KEY_ORDER_REMOVED_VEHICLES));
        $this->add($this->newOrderInput(ListVehicleController::QUERY_KEY_ORDER_CURRENT_VEHICLES));
    }

    /**
     * @param string $name
     * @return Input
     */
    protected function newInput(string $name): Input
    {
        $input = new Input($name);
        $input->setContinueIfEmpty(true);
        return $input;
    }

    /**
     * @param string $name
     * @param array $validColumnNames
     * @return Input
     */
    protected function newSortColumnInput(string $name, array $validColumnNames): Input
    {
        $input = $this->newInput($name);
        $input->setRequired(false);

        $sortValidatorChain = $input->getValidatorChain();

        $inArrayValidator = new InArray();
        $inArrayValidator->setHaystack($validColumnNames);
        $inArrayValidator->setMessages([InArray::NOT_IN_ARRAY => 'table.validation.error.sort.in-array']);
        $sortValidatorChain->attach($inArrayValidator);

        return $input;
    }

    /**
     * @param string $name
     * @return Input
     */
    protected function newOrderInput(string $name): Input
    {
        $input = $this->newInput($name);
        $input->setRequired(false);

        // Build validator chain
        $sortValidatorChain = $input->getValidatorChain();

        $inArrayValidator = new InArray();
        $inArrayValidator->setHaystack(['ASC', 'DESC']);
        $inArrayValidator->setMessages([InArray::NOT_IN_ARRAY => 'table.validation.error.order.in-array']);
        $sortValidatorChain->attach($inArrayValidator);

        // Build filter chain
        $sortFilterChain = $input->getFilterChain();
        $sortFilterChain->attach(new StringToUpper());

        return $input;
    }

    /**
     * @todo move to a parent
     *
     * @param array $input
     * @throws ValidationException
     */
    public static function validate(array $input)
    {
        $instance = new self();
        $instance->setData($input);
        if (! $instance->isValid()) {
            throw new ValidationException($instance->getMessages(), $input);
        }
    }
}
