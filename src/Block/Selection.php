<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionSelection\Block;

use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Registry;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;

/**
 * The selections of the options of a bundle product for the script of the product page, which finds the products of
 * the selected values and triggers events when they change.
 */
class Selection extends Template
{
    /** @var Product|null */
    private ?Product $product = null;

    /**
     * @param Template\Context $context
     * @param Registry $registryHelper
     * @param Json $json
     * @param Variables $variables
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        protected Registry $registryHelper,
        protected Json $json,
        protected Variables $variables,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Returns the product of the page.
     *
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        if ($this->product === null) {
            $product = $this->registryHelper->registry('product');

            $this->product = $product instanceof Product ? $product : null;
        }

        return $this->product;
    }

    /**
     * Returns the products of the selections of the options as JSON: option ID => selection ID => product ID.
     *
     * @return string
     */
    public function getIndex(): string
    {
        $index = [];

        $product = $this->getProduct();
        $typeInstance = $product?->getTypeInstance();

        if ($product !== null && $typeInstance instanceof Type) {
            $typeInstance->setStoreFilter($product->getStoreId(), $product);

            foreach ($typeInstance->getOptionsCollection($product) as $option) {
                if (! $option instanceof Option) {
                    continue;
                }

                $optionId = $this->variables->intValue($option->getId());

                foreach ($typeInstance->getSelectionsCollection([$optionId], $product) as $selection) {
                    if ($selection instanceof Product) {
                        $index[ $optionId ][ $this->variables->intValue($selection->getData('selection_id')) ] =
                            $this->variables->intValue($selection->getId());
                    }
                }
            }
        }

        return count($index) === 0 ? '{}' : ($this->json->encode($index) ?? '{}');
    }
}
