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

use MageINIC\StorePickup\Api\StorePickupRepositoryInterface as StorePickupRepository;
use MageINIC\StorePickup\Model\StorePickup\Image;
use MageINIC\StorePickupGraphQl\Model\StorePickup\SearchCriteria;
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
class StorePickupList implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var StorePickupRepository
     */
    protected StorePickupRepository $storePickupRepository;
    /**
     * @var Image
     */
    protected Image $image;
    /**
     * @var SearchCriteria
     */
    private SearchCriteria $searchCriteria;

    /**
     * FilterRecords Constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchCriteria $searchCriteria
     * @param StorePickupRepository $storePickupRepository
     * @param Image $image
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchCriteria        $searchCriteria,
        StorePickupRepository $storePickupRepository,
        Image                 $image
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchCriteria = $searchCriteria;
        $this->storePickupRepository = $storePickupRepository;
        $this->image = $image;
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
            $args = $this->searchCriteria->buildCriteria($args, $context->getExtensionAttributes()->getStore());
            $searchCriteria = $this->searchCriteriaBuilder->build('store_pickup', $args);
            $searchCriteria->setCurrentPage($args['currentPage']);
            $searchCriteria->setPageSize($pageSize);
            $collection = $this->storePickupRepository->getList($searchCriteria);

            $count = $collection->getTotalCount();
            $total_pages = ceil($count / $pageSize);

            if (!$count) {
                throw new GraphQlInputException(__('store pickup Does Not exist.'));
            }

            $storePickup = array_map(function ($value) {
                $holiday = implode(',', (array)$value->getHolidays());
                $value->setData("holiday_ids", $holiday);
                $value->setData("url", $value->getUrl());
                $value->setData("image", $this->image->getUrl($value));
                return $value;
            }, $collection->getItems());

            return [
                'total_count' => $count,
                'total_pages' => $total_pages,
                'storePickupList' => $storePickup
            ];

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    }
}
