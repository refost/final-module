<?php

namespace Drupal\final_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class that provide table form.
 */
class TableForm extends FormBase
{

  /**
   * This variable contains the Drupal service for translating text.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $t;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container)
  {
    $instance = parent::create($container);
    $instance->t = $container->get('string_translation');
    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId()
  {
    return 'table_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {

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
      'Q3',
      'Jul',
      'Aug',
      'Sep',
      'Q4',
      'Oct',
      'Nov',
      'Dec',
      'YTD',
    ];

    // Length of array.
    $length = count($titles) - 1;

    // Add one row if table have only header.
//    $count_rows = $form_state->get('count_rows');
//    if (empty($count_rows)) {
//      $count_rows = 1;
//      $form_state->set('count_rows', $count_rows);
//    }

    $tables = $form_state->get('tables');
    if (empty($tables)) {
      $tables = [1];
      $form_state->set('tables', $tables);
    }

//    $num_tabs = $form_state->get('num_tabs');
//    if (empty($num_tabs)) {
//      $num_tabs = 1;
//      $form_state->set('num_tabs', $num_tabs);
//    }

//    for ($num = 0; $num < $num_tabs; $num++) {
//    // Create header of table.
//    $form['table'][$num] = [
//      '#type' => 'table',
//      '#header' => $titles,
//      '#attributes' => [
//        'id' => 'table-form',
//      ],
//    ];
//
//    // Cycle for rows.
//    for ($j = 0; $j < $count_rows; ++$j) {
//
//      // Cycle for columns.
//      for ($i = 0; $i <= $length; $i++) {
//        if ($i == 0) {
//          $form['table'][$num][$j][$titles[$i]] = [
//            '#type' => 'number',
//            '#default_value' => date('Y') - $j,
//            '#disabled' => TRUE,
//          ];
//        }
//        elseif ($i == 4 || $i == 8 || $i == 12 || $i == 16 || $i == $length) {
//          $form['table'][$num][$j][$titles[$i]] = [
//            '#type' => 'number',
//            '#default_value' => NULL,
//            '#disabled' => TRUE,
//          ];
//        }
//        else {
//          $form['table'][$num][$j][$titles[$i]] = [
//            '#type' => 'number',
//          ];
//        }
//      }
//    }
//      // A function call button that adds a new row.
//      $form['action']['addYear'] = [
//        '#type' => 'submit',
//        '#value' => $this->t('Add row'),
//        '#name' => $num,
//        '#submit' => [
//          '::addRow',
//        ],
//      ];
//    }

    $tab_num = count($tables) - 1;

    for ($num = 0; $num <= $tab_num; $num++) {
      // Create header of table.
      $form[$num]['table'] = [
        '#type' => 'table',
        '#header' => $titles,
        '#tree' => TRUE,
        '#attributes' => [
          'id' => 'table-form',
        ],
      ];

      // Cycle for rows.
      for ($j = 0; $j < $tables[$num]; ++$j) {

        // Cycle for columns.
        for ($i = 0; $i <= $length; $i++) {
          if ($i == 0) {
            $form[$num]['table'][$j][$titles[$i]] = [
              '#type' => 'number',
              '#default_value' => date('Y') - $j,
              '#disabled' => TRUE,
            ];
          } elseif ($i == 4 || $i == 8 || $i == 12 || $i == 16 || $i == $length) {
            $form[$num]['table'][$j][$titles[$i]] = [
              '#type' => 'number',
              '#default_value' => NULL,
              '#disabled' => TRUE,
            ];
          } else {
            $form[$num]['table'][$j][$titles[$i]] = [
              '#type' => 'number',
            ];
          }
        }
      }
      // A function call button that adds a new row.
      $form[$num]['action']['addYear'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add row'),
        '#name' => $num,
        '#submit' => [
          '::addRow',
        ],
      ];
    }


    // A function call button that adds a new table.
    $form['action']['addTable'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Table'),
      '#submit' => [
        '::addTable',
      ],
    ];

    return $form;
  }

  /**
   * Function that add new row to the table.
   */
  public function addRow(array &$form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    $tables = $form_state->get('tables');
    $tables[$element['#name']]++;
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

  public function validateForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->addStatus('sdfsdfsdfdsf');
    parent::validateForm($form, $form_state); // TODO: Change the autogenerated stub
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
  }
}
