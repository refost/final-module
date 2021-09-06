<?php

namespace Drupal\final_module\Form;

use Drupal\block_content\Plugin\Menu\LocalAction\BlockContentAddLocalAction;
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
   * Check! Function that validate table.
   */
  public function validate(array &$form, FormStateInterface $form_state) {

    // Check!Get number of tables and number of rows.
    $tables = $form_state->get('tables');
    $table_count = count($tables);

    // Check! get all tables.
    $all_tables = $form_state->getValues();

    $all_result = [];

    // Check!Cycle for tables.
    for ($num = 0; $num < $table_count; $num++) {

      // Check! array with all rows.
      $all_values = [];
      // Check! cycle that convert associative array in normal.
      for ($i = 0; $i < $tables[$num]; $i++) {
        foreach ($all_tables[$num][$i] as $key => $value) {
          if ($key != 'Year' && $key != 'Q1' && $key != 'Q2' && $key != 'Q3' && $key != 'Q4' && $key != 'YTD') {
            $all_values[] = $value;
          }
        }
      }
      $all_values[] = "";
      $numb_cols = count($all_values) - 1;

      $st_point = NULL;
      $end_point = NULL;

      // Check! Table validate.
      for ($j = 0; $j < $numb_cols; $j++) {

        // Check! Start point.
        if ($j == 0 && ($all_values[$j] !== "")) {
          $st_point = $j;
        }
        elseif (($all_values[$j] === "") && ($all_values[$j + 1] !== "")) {
          if ($st_point === NULL) {
            $st_point = $j + 1;
          }
          elseif ($st_point !== NULL) {
            \Drupal::messenger()->addStatus("ERROR");
            break;
          }
        }

        // Check! End point.
        if (($all_values[$j] !== "") && ($all_values[$j + 1] === "")) {

          if ($end_point === NULL) {
            $end_point = $j;
          }
          elseif ($end_point !== NULL) {
            \Drupal::messenger()->addStatus("ERROR");
            break;
          }
        }
        elseif ($j == ($numb_cols - 1) && ($all_values[$j] !== "")) {
          if ($st_point === NULL) {
            $st_point = $j;
          }
          elseif (($end_point !== NULL) || ($all_values[$j-1] === "")) {
            \Drupal::messenger()->addStatus("ERROR");
            break;
          }
          elseif ($end_point === NULL) {
            $end_point = $j;
          }
        }
      }

      $all_result[] = [$st_point, $end_point];

    }

    // If all tables
    $size = array_unique($tables);

    if ($size == 1 && count($size) == 1) {
      
    }

  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
