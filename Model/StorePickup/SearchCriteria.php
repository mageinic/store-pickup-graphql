<?php
/**
 * MageINIC
 * Copyright (C) 2023. MageINIC <support@mageinic.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://opensource.org/licenses/gpl-3.0.html.
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category MageINIC
 * @package MageINIC_StorePickupGraphQl
 * @copyright Copyright (c) 2023. MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

declare(strict_types=1);

namespace MageINIC\StorePickupGraphQl\Model\StorePickup;

use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Filter;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Sort;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class for StorePickup Output Processor
 */
class SearchCriteria
{
    /**
     * Transform raw criteria data into SearchCriteriaInterface
     *
     * @param array $criteria
     * @param StoreInterface $store
     * @return array
     */
    public function buildCriteria(array $criteria, StoreInterface $store): array
    {
        $criteria[Filter::ARGUMENT_NAME][StorePickupInterface::STORE] = ['in' => $store->getId()];
        $criteria[Filter::ARGUMENT_NAME][StorePickupInterface::IS_ACTIVE] = ['eq' => true];
        $criteria[Sort::ARGUMENT_NAME][StorePickupInterface::POSITION] = [SortOrder::SORT_ASC];
        $criteria['pageSize'] = $criteria['pageSize'] ?? 6;
        $criteria['currentPage'] = $criteria['currentPage'] ?? 1;

        return $criteria;
    }
}
