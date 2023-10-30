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

namespace MageINIC\StorePickupGraphQl\Plugin\Model\Cart;

use MageINIC\StorePickup\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\QuoteGraphQl\Model\Cart\AssignShippingMethodToCart;
use Magento\QuoteGraphQl\Model\Cart\SetShippingMethodsOnCart as SetShippingMethods;

/**
 * class for SetShippingMethodsOnCart around plugin
 */
class SetShippingMethodsOnCart
{
    /**
     * @var AssignShippingMethodToCart
     */
    private AssignShippingMethodToCart $assignShippingMethodToCart;

    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * SetShippingMethodsOnCart Constructor.
     *
     * @param AssignShippingMethodToCart $assignShippingMethodToCart
     * @param Data $helperData
     */
    public function __construct(
        AssignShippingMethodToCart $assignShippingMethodToCart,
        Data $helperData
    ) {
        $this->assignShippingMethodToCart = $assignShippingMethodToCart;
        $this->helperData = $helperData;
    }

    /**
     * Around Plugin on Execute Methode.
     *
     * @param SetShippingMethods $subject
     * @param callable $proceed
     * @param ContextInterface $context
     * @param CartInterface $cart
     * @param array $shippingMethodsInput
     * @return void
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function aroundExecute(
        SetShippingMethods $subject,
        callable           $proceed,
        ContextInterface   $context,
        CartInterface      $cart,
        array              $shippingMethodsInput
    ): void {
        if (count($shippingMethodsInput) > 1) {
            throw new GraphQlInputException(
                __('You cannot specify multiple shipping methods.')
            );
        }
        $shippingMethodInput = current($shippingMethodsInput);

        if (empty($shippingMethodInput['carrier_code'])) {
            throw new GraphQlInputException(__('Required parameter "carrier_code" is missing.'));
        }
        $carrierCode = $shippingMethodInput['carrier_code'];

        if (empty($shippingMethodInput['method_code'])) {
            throw new GraphQlInputException(__('Required parameter "method_code" is missing.'));
        }
        $methodCode = $shippingMethodInput['method_code'];

        if ($carrierCode == 'mageinic_store_pickup' && $methodCode == 'mageinic_store_pickup') {

            if (empty($shippingMethodInput['store_pickup'][0]['store_pickup_id'])) {
                throw new GraphQlInputException(__('Required parameter "store_pickup_id" is missing.'));
            }

            if (empty($shippingMethodInput['store_pickup'][0]['pickup_date'])) {
                throw new GraphQlInputException(__('Required parameter "pickup_date" is missing.'));
            }

            $pickupId = $shippingMethodInput['store_pickup'][0]['store_pickup_id'];

            $shippingAddress = $cart->getShippingAddress()
                ->addData($this->helperData->getPickupStoreAddress($pickupId));

            $cart->setData('store_pickup_id', $pickupId);
            $cart->setData('store_pickup_date', $pickupId);

        } else {
            $shippingAddress = $cart->getShippingAddress();
        }
        $this->assignShippingMethodToCart->execute($cart, $shippingAddress, $carrierCode, $methodCode);
    }
}
