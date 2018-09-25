<?php

namespace DeInternetJongens\LighthouseUtils\Schema\Scalars;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\EmailValidation;
use Egulias\EmailValidator\Validation\RFCValidation;
use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

/**
 * To validate e-mails we're using https://github.com/egulias/EmailValidator, this is a wrapper for the e-mail validation function provided by: http://isemail.info/
 * This package checks against RFC 5321 whereas `filter_var()` does not.
 */
class Email extends ScalarType
{
    /** @var string */
    public $name = 'Email';

    /** @var string */
    public $description = 'A valid RFC 5321 compliant e-mail.';

    /** @var EmailValidator */
    private $emailValidator;

    /** @var EmailValidation */
    private $validation;

    public function __construct()
    {
        $this->emailValidator = new EmailValidator();
        $this->validation = new RFCValidation();
    }

    /**
     * @inheritDoc
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function parseValue($value)
    {
        if (! $this->emailValidator->isValid($value, $this->validation)) {
            throw new Error(sprintf('Input error: Expected valid e-mail, got: [%s]', $value));
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function parseLiteral($valueNode, array $variables = null)
    {
        if (! $valueNode instanceof StringValueNode) {
            throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
        }

        return $this->parseValue($valueNode->value);
    }
}
