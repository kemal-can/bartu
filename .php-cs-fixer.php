<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->notPath('bootstrap/cache')
    ->notPath('storage')
    ->notPath('vendor')
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config)
    ->setUsingCache(false)
    ->setRules([
        '@PSR2'                                    => true,
        'array_syntax'                             => ['syntax' => 'short'],
        'types_spaces'                             => ['space' => 'none'],
        'single_quote'                             => true,
        'no_useless_else'                          => true,
        'assign_null_coalescing_to_coalesce_equal' => true,
        'trailing_comma_in_multiline'              => ['elements' => ['arrays', 'parameters']],
        'blank_line_before_statement'              => true,
        'space_after_semicolon'                    => true,
        'whitespace_after_comma_in_array'          => true,
        'class_attributes_separation'              => true,
        'linebreak_after_opening_tag'              => true,
        'combine_consecutive_unsets'               => true,
        'explicit_indirect_variable'               => true,
        'method_chaining_indentation'              => true,
        'no_empty_statement'                       => true,
        'indentation_type'                         => true,
        'no_leading_import_slash'                  => true,
        'no_leading_namespace_whitespace'          => true,
        'echo_tag_syntax'                          => ['format' => 'long'],
        'single_blank_line_before_namespace'       => true,
        'single_line_after_imports'                => true,
        'blank_line_after_opening_tag'             => true,
        'no_blank_lines_after_class_opening'       => true,
        'not_operator_with_successor_space'        => true,
        'phpdoc_trim'                              => true,
        'simplified_if_return'                     => true,
        'phpdoc_align'                             => ['align' => 'left'],
        'no_unused_imports'                        => true,
        'return_type_declaration'                  => ['space_before' => 'one'],
        'binary_operator_spaces'                   => [
            'operators' => [
                '=>' => 'align_single_space_minimal',
                '='  => 'align_single_space_minimal',
            ],
        ],
        'concat_space' => ['spacing' => 'one'],
])->setFinder($finder);
