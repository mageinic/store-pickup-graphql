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
 * @package MageINIC_StorePickUpGraphQl
 * @copyright Copyright (c) 2023. MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

namespace MageINIC\StorePickupGraphQl\Model\Resolver;

use MageINIC\StorePickup\Api\HolidaysRepositoryInterface as HolidayRepository;
use MageINIC\StorePickupGraphQl\Model\Holiday\SearchCriteria;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 */
class HolidayList implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var HolidayRepository
     */
    protected HolidayRepository $storePickupRepository;

    /**
     * @var SearchCriteria
     */
    private SearchCriteria $searchCriteria;

    /**
     * FilterRecords Constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param HolidayRepository $storePickupRepository
     * @param SearchCriteria $searchCriteria
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        HolidayRepository $storePickupRepository,
        SearchCriteria $searchCriteria
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storePickupRepository = $storePickupRepository;
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        try {
            $pageSize = $args['pageSize'];
            $args = $this->searchCriteria->buildCriteria($args);

            $searchCriteria = $this->searchCriteriaBuilder->build('store_holiday', $args);
            $searchCriteria->setCurrentPage($args['currentPage']);
            $searchCriteria->setPageSize($pageSize);
            $collection = $this->storePickupRepository->getList($searchCriteria);

            $count = $collection->getTotalCount();
            $total_pages = ceil($count / $pageSize);

            if (!$count) {
                throw new GraphQlInputException(__('store Holiday does Not exist.'));
            }

            $storeHoliday = array_map(function ($value) {
                return $value;
            }, $collection->getItems());

            return [
                'total_count' => $count,
                'total_pages' => $total_pages,
                'holidayList' => $storeHoliday
            ];
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    }
}
