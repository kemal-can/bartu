<?php
/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */

namespace App\Innoclapps\Import;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Validator;
use App\Innoclapps\Rules\UniqueResourceRule;
use App\Innoclapps\Resources\Http\ImportRequest;

trait ValidatesFields
{
    /**
     * Validates the rows
     *
     * @throws \App\Innoclapps\Import\Exceptions\ValidationException
     *
     * @param array $rows
     *
     * @return array
     */
    protected function prepareRequestsForValidation(array $rows)
    {
        $request = resolve(ImportRequest::class);

        return $this->createRequestsCollection($rows, $request);
    }

    /**
     * Prepare the validator for the given rows
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function createValidator()
    {
        return Validator::make(
            [],
            $this->resolveValidationRules(),
            $this->resolveValidationMessages(),
            $this->createCustomAttributes()
        );
    }

    /**
     * Create fake requests collection
     *
     * @return \Illuminate\Support\LazyCollection
     */
    protected function createRequestsCollection($rows, $sampleRequest)
    {
        // We will create lazy collection because of the weight of the requests
        return LazyCollection::make(function () use ($rows, $sampleRequest) {
            $i = 0;
            $count = count($rows);
            $validator = $this->createValidator();

            while ($i < $count) {
                $request = (clone $sampleRequest)
                    ->setFields($this->resolveFields())
                    ->replace($rows[$i])
                    ->runValidationCallbacks($validator)
                    ->setOriginal($rows[$i]);

                // When the validation callbacks are executed
                // We will set the actual validator data, perhaps the validation
                // callback have modified the value in this case, this will ensure a proper validation
                yield $request->setValidator(
                    $validator->setData($request->all())
                );
                $i++;
            }
        });
    }

    /**
    * Get the error messages for import
    *
    * @return array
    */
    protected function resolveValidationMessages() : array
    {
        return $this->resolveFields()->map(function ($field) {
            return $field->prepareValidationMessages();
        })->filter()
            ->collapse()
            ->mapWithKeys(function ($message, $attribute) {
                return [$attribute => $message];
            })
            ->all();
    }

    /**
     * Create custom attributes for the validation rules
     *
     * @return array
     */
    protected function createCustomAttributes() : array
    {
        return $this->resolveFields()->mapWithKeys(function ($field) {
            return [$field->attribute => Str::lower(strip_tags($field->label))];
        })->all();
    }

    /**
     * Get the import validation rules
     *
     * @return array
     */
    protected function resolveValidationRules() : array
    {
        return $this->resolveFields()->mapWithKeys(function ($field) {
            $rules = $field->getImportRules();
            $attribute = array_key_first($rules);

            return [
                $attribute => collect($rules[$attribute])->reject(function ($rule) {
                    return $rule instanceof UniqueResourceRule && $rule->skipOnImport;
                }),
            ];
        })->all();
    }
}
