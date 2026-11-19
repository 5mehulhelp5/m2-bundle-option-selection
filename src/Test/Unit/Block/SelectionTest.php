<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionSelection\Test\Unit\Block;

use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\BundleOptionSelection\Block\Selection;
use Infrangible\Core\Helper\Registry;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\TestCase;

/**
 * Tests the products of the selections of the options of a bundle product for the script of the product page.
 */
class SelectionTest extends TestCase
{
    /**
     * Returns the block with the product of the page.
     *
     * @param Product|null $product
     *
     * @return Selection
     */
    private function createBlock(?Product $product): Selection
    {
        $registry = $this->createStub(Registry::class);
        $registry->method('registry')->willReturn($product);

        return new Selection($this->createStub(Context::class), $registry, new Json(), new Variables());
    }

    /**
     * Returns a product with a selection ID.
     *
     * @param int $productId
     * @param int $selectionId
     *
     * @return Product
     */
    private function createSelection(int $productId, int $selectionId): Product
    {
        $selection = $this->createStub(Product::class);
        $selection->method('getId')->willReturn((string)$productId);
        $selection->method('getData')->willReturn((string)$selectionId);

        return $selection;
    }

    /**
     * Tests the index: option ID => selection ID => product ID.
     *
     * @return void
     */
    public function testIndex(): void
    {
        $options = [];

        foreach ([1, 2] as $optionId) {
            $option = $this->createStub(Option::class);
            $option->method('getId')->willReturn((string)$optionId);
            $options[] = $option;
        }

        $typeInstance = $this->createStub(Type::class);
        $typeInstance->method('getOptionsCollection')->willReturn($options);
        $typeInstance->method('getSelectionsCollection')->willReturnCallback(
            fn(array $optionIds): array => $optionIds === [1] ?
                [$this->createSelection(10, 100), $this->createSelection(11, 101)] : [$this->createSelection(12, 102)]
        );

        $product = $this->createStub(Product::class);
        $product->method('getTypeInstance')->willReturn($typeInstance);

        $this->assertSame('{"1":{"100":10,"101":11},"2":{"102":12}}', $this->createBlock($product)->getIndex());
    }

    /**
     * Tests that a page without bundle product has no index; before, the block failed without product.
     *
     * @return void
     */
    public function testWithoutBundleProduct(): void
    {
        $product = $this->createStub(Product::class);
        $product->method('getTypeInstance')->willReturn($this->createStub(Product\Type\Simple::class));

        $this->assertSame('{}', $this->createBlock($product)->getIndex());
        $this->assertSame('{}', $this->createBlock(null)->getIndex());
    }
}
