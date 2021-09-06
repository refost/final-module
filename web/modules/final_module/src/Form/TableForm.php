<?php

namespace Drupal\final_module\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class that provide table form.
 */
class TableForm extends FormBase {

  /**
   * This variable contains the Drupal service for translating text.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $t;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->t = $container->get('string_translation');
    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'table_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Array with title for cells.
    $titles = [
      'Year',
      'Jan',
      'Feb',
      'Mar',
      'Q1',
      'Apr',
      'May',
      'Jun',
      'Q2',
      'Jul',
      'Aug',
      'Sep',
      'Q3',
      'Oct',
      'Nov',
      'Dec',
      'Q4',
      'YTD',
    ];

    // Length of array with titles. Used to build columns in tables.
    $length = count($titles) - 1;

    // Array with tables properties.
    $tables = $form_state->get('tables');
    if (empty($tables)) {

      // The position of the number is the number of the table.
      // The number in the array is the amount of rows.
      $tables = [1];
      $form_state->set('tables', $tables);
    }

    $form['#prefix'] = '<div id="form-with-table">';
    $form['#suffix'] = '</div>';

    $form['btn-container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'btn-container',
      ],
    ];

    // A function call button that adds a new table.
    $form['btn-container']['addTable'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Table'),
      '#name' => 'addTable',
      '#submit' => [
        '::addTable',
      ],
      '#ajax' => [
        'wrapper' => 'form-with-table',
      ],
    ];

    // A function call button that adds a new table.
    $form['btn-container']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#name' => 'send',
      '#ajax' => [
        'callback' => '::validate',
      ],
    ];

    $tab_num = count($tables) - 1;

    // Tables building loop. $num is the table number.
    for ($num = 0; $num <= $tab_num; $num++) {

      // A function call button that adds a new row.
      $form['table']['addYear' . $num] = [
        '#type' => 'submit',
        '#value' => $this->t('Add row'),

        // #name contains the number of tables. One is added because
        // in a different way #name on the first iteration will be NULL.
        '#name' => $num + 1,
        '#submit' => [
          '::addRow',
        ],
        '#ajax' => [
          'wrapper' => 'form-with-table',
        ],
      ];

      // Create header of table.
      $form['table'][$num] = [
        '#type' => 'table',
        '#header' => $titles,
        '#attributes' => [
          'id' => 'table-form-' . $num,
        ],
      ];

      // Cycle for rows.
      for ($j = 0; $j < $tables[$num]; $j++) {

        // Cycle for columns.
        for ($i = 0; $i <= $length; $i++) {
          if ($i == 0) {
            $form['table'][$num][$j][$titles[$i]] = [
              '#type' => 'number',
              '#default_value' => date('Y') - $j,
              '#disabled' => TRUE,
            ];
          }
          elseif ($i == 4|| $i == 8 || $i == 12 || $i == 16 || $i == $length) {
            $form['table'][$num][$j][$titles[$i]] = [
              '#type' => 'number',
              '#default_value' => NULL,
              '#disabled' => TRUE,
            ];
          }
          else {
            $form['table'][$num][$j][$titles[$i]] = [
              '#type' => 'number',
            ];
          }
        }
      }
    }

    return $form;
  }

  /**
   * Function that add new row to the table.
   */
  public function addRow(array &$form, FormStateInterface $form_state) {

    // Getting a pressed button.
    $element = $form_state->getTriggeringElement();
    $tables = $form_state->get('tables');

    // Minus one because #name is number of table + 1.
    $tables[$element['#name'] - 1]++;
    $form_state->set('tables', $tables);
    $form_state->setRebuild();
  }

  /**
   * Function that add new row to the table.
   */
  public function addTable(array &$form, FormStateInterface $form_state) {
    $tables = $form_state->get('tables');
    $tables[] = 1;
    $form_state->set('tables', $tables);
    $form_state->setRebuild();
  }

  /**
   * Function.
   */
  public function convertArray($length, $table) {
    for ($i = 0; $i < $length; $i++) {
      foreach ($table[$i] as $key => $value) {
        if ($key != 'Year' && $key != 'Q1' && $key != 'Q2' && $key != 'Q3' && $key != 'Q4' && $key != 'YTD') {
          $all_values[] = $value;
        }
      }
    }
    return $all_values;
  }

  /**
   * Function that validate table.
   */
  public function validate(array &$form, FormStateInterface $form_state) {

    // Getting an array with tables properties.
    $tables = $form_state->get('tables');
    $table_count = count($tables);
    $all_tables = $form_state->getValues();

    // An array for the start and end points in the table.
    // The starting point is where non-blank fields start from.
    // The end point is the end of non-empty fields.
    $all_result = [];

    $non_error = TRUE;

    // Loop that loops through the tables.
    for ($num = 0; $num < $table_count; $num++) {

      // An array for all cells in the table.
      $all_values = $this->convertArray($tables[$num], $all_tables[$num]);

      // There must be one more element in the array to find
      // st_point correctly.
      $all_values[] = "";

      // Now the counter and array have the same number of elements.
      $numb_cols = count($all_values) - 1;

      // The point at which non-empty fields begin.
      $st_point = NULL;

      // The ending point of non-empty fields.
      $end_point = NULL;

      for ($j = 0; $j < $numb_cols; $j++) {

        // Checking point at which non-empty fields begin.
        // If point is the first in the array.
        if ($j == 0 && ($all_values[$j] !== "")) {
          $st_point = $j;
        }

        // Else if point is the not first in the array.
        elseif (($all_values[$j] === "") && ($all_values[$j + 1] !== "")) {
          if ($st_point === NULL) {
            $st_point = $j + 1;
          }
          elseif ($st_point !== NULL) {
            $non_error = FALSE;
            break 2;
          }
        }

        // Checking the ending point of non-empty fields.
        // If point is the last in the array.
        if (($j == ($numb_cols - 1)) && ($all_values[$j] !== "")) {
          if ($end_point === NULL) {
            $end_point = $j;
          }
        }

        // If point is the not last in the array.
        elseif (($all_values[$j] !== "") && ($all_values[$j + 1] === "")) {
          if ($end_point === NULL) {
            $end_point = $j;
          }
        }
      }

      // An array containing the start and end points for all tables.
      $all_result[] = [
        $st_point,
        $end_point,
      ];

    }

    $same = array_unique($tables);

    // If all tables have 1 row then $same will have one element equal to 1.
    if ($same[0] == 1 && count($same) == 1 && $non_error === TRUE) {

      // Compare ranges of values.
      for ($i = 0; $i < count($all_result); $i++) {
        if ($all_result[0] != $all_result[$i]) {
          $non_error = FALSE;
        }
      }

    }

    if ($non_error === TRUE) {
      $response = new AjaxResponse();
      $response->addCommand(
        new MessageCommand(
          'VALID',
          '.table-form',
          [
            'type' => 'status',
          ]
        )
      );
      \Drupal::messenger()->addStatus('VALID');
      $form_state->setRebuild();
    }
    else {
      \Drupal::messenger()->addError('INVALID');
    }

  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
