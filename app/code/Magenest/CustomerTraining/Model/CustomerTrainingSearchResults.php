<?php
/**
 * app/code/Magenest/CustomerTraining/Model/CustomerTrainingSearchResults.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Model;

use Magenest\CustomerTraining\Api\Data\CustomerTrainingSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class CustomerTrainingSearchResults extends SearchResults implements CustomerTrainingSearchResultsInterface
{
}
