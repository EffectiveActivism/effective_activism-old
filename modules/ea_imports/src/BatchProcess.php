<?php

namespace Drupal\ea_imports;

use Drupal\ea_imports\Parser\ParserInterface;
use Drupal\ea_imports\Entity\Import;

/**
 * Processes batches of item imports.
 */
class BatchProcess {

  /**
   * Imports from a parser.
   *
   * @param Drupal\ea_imports\Parser\ParserInterface $parser
   *   The parser object to import items with.
   * @param Drupal\ea_imports\Entity\Import $entity
   *   The import entity to add event references to.
   * @param array $context
   *   The context.
   */
  public static function import(ParserInterface $parser, Import $entity, &$context) {
    // Set inital batch values.
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 1;
    }
    $context['message'] = t('Importing items...');
    // Reload entity to get latest changes.
    $entity = Import::load($entity->id());
    foreach ($parser->getNextBatch($context['sandbox']['progress']) as $item) {
      $itemId = $parser->importItem($item);
      if (intval($itemId) > 0 && !in_array($itemId, $context['results'])) {
        $entity->events->appendItem($itemId);
      }
      $context['results'][] = $itemId;
      $context['sandbox']['progress']++;
    }
    $entity->save();
    // Inform batch about progess.
    $context['finished'] = $context['sandbox']['progress'] / $parser->getItemCount();
  }

  /**
   * Finishes a batch call.
   *
   * @param bool $success
   *   Wether or not any fatal PHP errors were encountered.
   * @param array $results
   *   The result of the import.
   * @param array $operations
   *   The operations performed.
   */
  public static function finished($success, $results, $operations) {
    if ($success) {
      drupal_set_message(\Drupal::translation()->formatPlural(
        count(array_unique($results, SORT_NUMERIC)),
        'One item imported.',
        '@count items imported.'
      ));
    }
    else {
      drupal_set_message(t('An error occured during import.'), 'error');
    }
  }

}
