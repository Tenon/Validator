<?php
namespace Tenon\Validator\Contracts;

interface ValidatorContract
{
    public function getExplodedRules(): array;

    public function passes(): bool;
}