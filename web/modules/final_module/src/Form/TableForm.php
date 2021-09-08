<?php

namespace Drupal\final_module\Form;

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

      $values = $form_state->getValues();

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
    $form['btn-container']['Submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#name' => 'submit',
      '#ajax' => [
        'wrapper' => 'form-with-table ',
      ],
    ];

    $amount_tab = count($tables) - 1;

    // Tables building loop. $num is the table number.
    for ($tab = 0; $tab <= $amount_tab; $tab++) {

      // A function call button that adds a new row.
      $form['table']['addYear' . $tab] = [
        '#type' => 'submit',
        '#value' => $this->t('Add Year'),

        // #name contains the number of tables. One is added because
        // in a different way #name on the first iteration will be NULL.
        '#name' => $tab + 1,
        '#submit' => [
          '::addRow',
        ],
        '#ajax' => [
          'wrapper' => 'form-with-table',
        ],
      ];

      // Create header of table.
      $form['table']['tab' . $tab] = [
        '#type' => 'table',
        '#header' => $titles,
        '#attributes' => [
          'id' => 'table-form-' . $tab,
        ],
      ];

      // Cycle for rows.
      for ($row = 0; $row < $tables[$tab]; $row++) {

        // Cycle for columns.
        for ($col = 0; $col <= $length; $col++) {

          if ($col == 0) {
            $form['table']['tab' . $tab][$row][$titles[$col]] = [
              '#type' => 'number',
              '#default_value' => date('Y') - $row,
              '#disabled' => TRUE,
            ];
          }
          elseif ($col == 4|| $col == 8 || $col == 12 || $col == 16 || $col == $length) {
            $value = $form_state->getValue('tab' . $tab)[$row][$titles[$col]];
            $form['table']['tab' . $tab][$row][$titles[$col]] = [
              '#type' => 'number',
              '#default_value' => ($value != 0) ? round($value, 2) : 0,
              '#disabled' => TRUE,
              '#step' => 0.01,
            ];
          }
          else {
            $form['table']['tab' . $tab][$row][$titles[$col]] = [
              '#type' => 'number',
              '#step' => 0.00001,
            ];
          }

        }

      }

    }

    $form['#attached']['library'][] = 'final_module/finalModule';
    return $form;
  }

  /**
   * The function of adding a new row to the table.
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
   * The function of adding a new table to the form.
   */
  public function addTable(array &$form, FormStateInterface $form_state) {
    $tables = $form_state->get('tables');
    $tables[] = 1;
    $form_state->set('tables', $tables);
    $form_state->setRebuild();
  }

  /**
   * Function for converting an associative array to normal.
   *
   * @param array $array
   *   Associative array.
   * @param int $length
   *   Length of array.
   *
   * @return mixed
   *   Normal array.
   */
  public function convertArray($length, $array) {
    for ($i = 0; $i < $length; $i++) {
      foreach ($array[$i] as $key => $value) {
        if ($key != 'Year' && $key != 'Q1' && $key != 'Q2' && $key != 'Q3' && $key != 'Q4' && $key != 'YTD') {
          $all_values[] = $value;
        }
      }
    }
    return $all_values;
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $name = $form_state->getTriggeringElement()['#name'];

    if ($name == 'submit') {
      // Getting an array with tables properties.
      $tables = $form_state->get('tables');
      $amount_tab = count($tables);
      $all_tables = $form_state->getValues();

      // An array for the start and end points in the table.
      // The starting point is where non-blank fields start from.
      // The end point is the end of non-empty fields.
      $all_result = [];

      $non_error = TRUE;

      // Loop that loops through the tables.
      for ($tab = 0; $tab < $amount_tab; $tab++) {

        // An array for all cells in the table.
        $all_values = $this->convertArray($amount_tab, $all_tables['tab' . $tab]);

        // There must be one more element in the array to find
        // st_point correctly.
        $all_values[] = "";

        // Now the counter and array have the same number of elements.
        $amount_cols = count($all_values) - 1;

        // The point at which non-empty fields begin.
        $st_point = NULL;

        // The ending point of non-empty fields.
        $end_point = NULL;

        for ($col = 0; $col < $amount_cols; $col++) {

          // Checking point at which non-empty fields begin.
          // If point is the first in the array.
          if ($col == 0 && ($all_values[$col] !== "")) {
            $st_point = $col;
          }

          // Else if point is the not first in the array.
          elseif (($all_values[$col] === "") && ($all_values[$col + 1] !== "")) {
            if ($st_point === NULL) {
              $st_point = $col + 1;
            }
            elseif ($st_point !== NULL) {
              $form_state->setError($form['table'], 'Invalid');
              $non_error = FALSE;
              break 2;
            }
          }

          // Checking the ending point of non-empty fields.
          // If point is the last in the array.
          if (($col == ($amount_cols - 1)) && ($all_values[$col] !== "")) {
            if ($end_point === NULL) {
              $end_point = $col;
            }
          }

          // If point is the not last in the array.
          elseif (($all_values[$col] !== "") && ($all_values[$col + 1] === "")) {
            if ($end_point === NULL) {
              $end_point = $col;
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
            $form_state->setError($form['table'], 'Invalid');
            $non_error = FALSE;
            break;
          }
        }

      }

      if ($non_error === TRUE) {
        \Drupal::messenger()->addStatus('Valid');
      }
      return $non_error;
    }

  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $all_tables = $form_state->getValues();
    $tables = $form_state->get('tables');

    $amount_tab = count($tables);

    // Table calculation.
    for ($tab = 0; $tab < $amount_tab; $tab++) {
      $all_quarter = [];
      $all_values = $this->convertArray($tables[$tab], $all_tables['tab' . $tab]);
      $amount_val = count($all_values);

      // Quarter calculation.
      for ($pos_val = 0; $pos_val <= $amount_val; $pos_val++) {

        // At all points that are multiples of 3, we
        // have a specific field for the amount.
        if ($pos_val == 0 || $pos_val % 3 != 0) {
          $quarter += $all_values[$pos_val];
        }
        // If this is not done, then all fourth quarters will be
        // out of the row in the next position.
        elseif ($pos_val % 12 == 0) {
          $quarter = ($quarter + 1) / 3;
          $all_quarter[intdiv($pos_val, 12) - 1][] = $quarter;
          $quarter = $all_values[$pos_val];
        }
        else {
          $quarter = ($quarter + 1) / 3;
          $all_quarter[intdiv($pos_val, 12)][] = $quarter;
          $quarter = $all_values[$pos_val];
        }

      }

      $amount_quart = count($all_quarter);

      // YTD calculation and setting values in the form.
      for ($row = 0; $row < $amount_quart; $row++) {

        $year = 0;

        for ($element = 0; $element < count($all_quarter[$row]); $element++) {
          $year += $all_quarter[$row][$element];
          $form_state->setValueForElement($form['table']['tab' . $tab][$row]['Q' . ($element + 1)], $all_quarter[$row][$element]);
        }

        $year = ($year + 1) / 4;
        $form_state->setValueForElement($form['table']['tab' . $tab][$row]['YTD'], $year);

      }

    }

    $form_state->setRebuild();
  }

}
