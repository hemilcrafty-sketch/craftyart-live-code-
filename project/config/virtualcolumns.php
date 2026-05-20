<?php
return [
    'columns' => [
        [
          'column' => 'Category',
          'is_dependent' => true,
          'table_name' => 'categories',
          'column_name' => 'category_id',
          'dependent_column_name' => 'category_name',
          'dependent_column_id' => 'id',
          'type' => 'string'
        ],
        [
          'column' => 'New Category',
          'is_dependent' => true,
          'column_name' => 'new_category_id',
          'table_name' => 'new_categories',
          'dependent_column_name' => 'category_name',
          'dependent_column_id' => 'id',
          'type' => 'string'
        ],
        [
          'column' => 'Style',
          'is_dependent' => true,
          'table_name' => 'styles',
          'column_name' => 'style_id',
          'isMultiple' => true,
          'dependent_column_name' => 'name',
          'dependent_column_id' => 'id',
          'type' => 'string'
        ],
        [
          'column' => 'Interest',
          'is_dependent' => true,
          'table_name' => 'interests',
          'column_name' => 'interest_id',
          'isMultiple' => true,
          'dependent_column_name' => 'name',
          'dependent_column_id' => 'id',
          'type' => 'string'
        ],
        [
          'column' => 'Language',
          'is_dependent' => true,
          'table_name' => 'languages',
          'column_name' => 'lang_id',
          'isMultiple' => true,
          'dependent_column_name' => 'name',
          'dependent_column_id' => 'id',
          'type' => 'string'
        ],
        [
          'column' => 'Related Tags',
          'is_dependent' => true,
          'table_name' => 'search_tags',
          'column_name' => 'related_tags',
          'isMultiple' => true,
          'dependent_column_name' => 'name',
          'dependent_column_id' => 'name',
          'type' => 'string'
        ],
        [
          'column' => 'New Related Tags',
          'is_dependent' => true,
          'column_name' => 'new_related_tags',
          'table_name' => 'new_search_tags',
          'isMultiple' => true,
          'dependent_column_name' => 'name',
          'dependent_column_id' => 'id',
          'type' => 'string'
        ],
        [
          'column' => 'Special Keywords',
          'is_dependent' => true,
          'column_name' => 'special_keywords',
          'table_name' => 'special_keywords',
          'isMultiple' => true,
          'dependent_column_name' => 'name',
          'dependent_column_id' => 'id',
          'type' => 'string'
        ],
        [
          'column' => 'Themes',
          'is_dependent' => true,
          'table_name' => 'themes',
          'column_name' => 'theme_id',
          'isMultiple' => true,
          'dependent_column_name' => 'name',
          'dependent_column_id' => 'id',
          'type' => 'string'
        ],
        [
          'column' => 'Colors',
          'is_dependent' => true,
          'table_name' => 'colors',
          'column_name' => 'color_id',
          'isMultiple' => true,
          'dependent_column_name' => 'code',
          'dependent_column_id' => 'id',
          'type' => 'string'
        ],
        [
          'column' => 'Religions',
          'is_dependent' => true,
          'table_name' => 'religions',
          'column_name' => 'religion_id',
          'dependent_column_name' => 'religion_name',
          'isMultiple' => true,
          'dependent_column_id' => 'id',
          'type' => 'string'
        ],
        [
          'column' => 'Animation',
          'is_dependent' => false,
          'column_name' => 'animation',
          'dependent_column_name' => 'animation',
          'type' => 'boolean'
        ],
        [
          'column' => 'Size',
          'is_dependent' => true,
          'table_name' => 'sizes',
          'isMultiple' => false,
          'dependent_column_id' => 'id',
          'column_name' => 'template_size',
          'dependent_column_name' => 'size_name',
          'type' => 'string'
        ],
        [
          'column' => 'Date Range',
          'is_dependent' => false,
          'column_name' => 'date_range',
          'dependent_column_name' => 'date_range',
          'type' => 'datepicker'
        ],
        [
          'column' => 'views',
          'is_dependent' => false,
          'column_name' => 'views',
          'dependent_column_name' => 'views',
          'type' => 'number'
        ],
        [
          'column' => 'is_premium',
          'is_dependent' => false,
          'column_name' => 'is_premium',
          'dependent_column_name' => 'is_premium',
          'type' => 'boolean'
        ],
        [
          'column' => 'Pinned',
          'is_dependent' => false,
          'column_name' => 'pinned',
          'dependent_column_name' => 'pinned',
          'type' => 'boolean'
        ],
        [
          'column' => 'No Index',
          'is_dependent' => false,
          'column_name' => 'no_index',
          'dependent_column_name' => 'no_index',
          'type' => 'boolean'
        ],
        [
          'column' => 'orientation',
          'is_dependent' => false,
          'column_name' => 'orientation',
          'dependent_column_name' => 'orientation',
          'type' => 'string'
        ],
      ],
    'sorting' => [
        [
            'column' => 'Latest',
            'column_name' => 'id',
        ],
        [
            'column' => 'Animation',
            'column_name' => 'animation',
        ],
        [
            'column' => 'Views',
            'column_name' => 'views',
        ],
        [
            'column' => 'Premium',
            'column_name' => 'is_premium',
        ]
    ],
    'operators' => [
        '=' => 'Equals',
        '!=' => 'Not Equals',
        '>' => 'Greater Than',
        '<' => 'Less Than',
        '>=' => 'Greater Than or Equal',
        '<=' => 'Less Than or Equal',
        'LIKE' => 'Contains',
        'NOT LIKE' => 'Does Not Contain',
        'IN' => 'In List',
        'NOT IN' => 'Not in List',
        'RANGE' => 'Range',
        'SORT' => 'sort',
        'BETWEEN' => 'Between',
        'NOT BETWEEN' => 'Not Between',
        'IS NULL' => 'Is Null',
        'IS NOT NULL' => 'Is Not Null',
        'REGEXP' => 'Matches Regex',
        'NOT REGEXP' => 'Does Not Match Regex',
    ],
];