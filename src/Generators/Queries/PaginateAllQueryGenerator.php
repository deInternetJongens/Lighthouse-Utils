<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Queries;

use DeInternetJongens\LighthouseUtils\Schema\Scalars\Date;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\DateTimeTz;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use Nuwave\Lighthouse\Schema\Types\Scalars\DateTime;

class PaginateAllQueryGenerator
{
    /** @var array */
    private static $supportedGraphQLTypes = [
        IDType::class,
        StringType::class,
        IntType::class,
        FloatType::class,
        Date::class,
        DateTime::class,
        DateTimeTz::class
    ];

    /**
     * Generates GraphQL queries with arguments for each field
     * Returns a query for 'all' and 'paginated', depending on what kind of result you want
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @param array $supportedGraphQLTypes
     * @return string
     */
    public static function generate(string $typeName, array $typeFields): string
    {
        $arguments = [];

        foreach ($typeFields as $fieldName => $field) {
            $className = get_class($field);
            // We can generate queries for all but Object types, as Object types are relations
            if (! in_array($className, self::$supportedGraphQLTypes)) {
                continue;
            }

            // Add all our custom directives
            $arguments[] = sprintf('%s: %s @eq', $fieldName, $field->name);
            $arguments[] = sprintf('%s_not: %s @not', $fieldName, $field->name);
            $arguments[] = sprintf('%s_in: %s @in', $fieldName, $field->name);
            $arguments[] = sprintf('%s_not_in: %s @not_in', $fieldName, $field->name);
            $arguments[] = sprintf('%s_lt: %s @lt', $fieldName, $field->name);
            $arguments[] = sprintf('%s_lte: %s @lte', $fieldName, $field->name);
            $arguments[] = sprintf('%s_gt: %s @gt', $fieldName, $field->name);
            $arguments[] = sprintf('%s_gte: %s @gte', $fieldName, $field->name);

            if (\strtolower($field->name) === 'string') {
                $arguments[] = sprintf('%s_contains: %s @contains', $fieldName, $field->name);
                $arguments[] = sprintf('%s_not_contains: %s @not_contains', $fieldName, $field->name);
                $arguments[] = sprintf('%s_starts_with: %s @starts_with', $fieldName, $field->name);
                $arguments[] = sprintf('%s_not_starts_with: %s @not_starts_with', $fieldName, $field->name);
                $arguments[] = sprintf('%s_ends_with: %s @not_ends_with', $fieldName, $field->name);
            }
        }

        if (count($arguments) < 1) {
            return '';
        }

        $allQuery = '    ' . str_plural(strtolower($typeName));
        $queryArguments = sprintf('(%s)', implode(', ', $arguments));
        $allQuery .= sprintf('%1$s: [%2$s]! @all(model: "%2$s")', $queryArguments, $typeName);
        $allQuery .= sprintf(' @can(if: "findAll", model: "%1$s")', $typeName);

        $paginatedQuery = '    ' . str_plural(strtolower($typeName)) . 'Paginated';
        $paginatedQuery .= sprintf('%1$s: [%2$s]! @paginate(model: "%2$s")', $queryArguments, $typeName);
        $paginatedQuery .= sprintf(' @can(if: "paginate", model: "%1$s")', $typeName);

        return $allQuery ."\r\n". $paginatedQuery;
    }
}
