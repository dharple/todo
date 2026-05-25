<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Unit\Renderer;

use App\Renderer\DisplayConfig;
use Exception;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for DisplayConfig.
 */
class DisplayConfigTest extends TestCase
{
    /**
     * Verifies that the constructor does not apply non-saved fields (filterIds, showPriorityEditor).
     *
     * @return void
     */
    public function testConstructorIgnoresNonSavedFields(): void
    {
        $config = new DisplayConfig([
            'filterIds'          => [1, 2, 3],
            'showPriorityEditor' => true,
        ]);

        $this->assertSame([], $config->getFilterIds());
        $this->assertFalse($config->getShowPriorityEditor());
    }

    /**
     * Verifies that the constructor initializes fields from the provided array.
     *
     * @return void
     */
    public function testConstructorInitializesFromArray(): void
    {
        $config = new DisplayConfig([
            'filterAging'     => '30',
            'filterClosed'    => 'all',
            'filterDeleted'   => 'recently',
            'filterFreshness' => 'week',
            'filterPriority'  => 'high',
            'filterSection'   => 7,
            'showInactive'    => true,
            'showPriority'    => 'y',
        ]);

        $this->assertSame('30', $config->getFilterAging());
        $this->assertSame('all', $config->getFilterClosed());
        $this->assertSame('recently', $config->getFilterDeleted());
        $this->assertSame('week', $config->getFilterFreshness());
        $this->assertSame('high', $config->getFilterPriority());
        $this->assertSame(7, $config->getFilterSection());
        $this->assertTrue($config->getShowInactive());
        $this->assertSame('y', $config->getShowPriority());
    }

    /**
     * Verifies all default property values.
     *
     * @return void
     */
    public function testDefaults(): void
    {
        $config = new DisplayConfig();

        $this->assertSame('all', $config->getFilterAging());
        $this->assertSame('none', $config->getFilterClosed());
        $this->assertSame('none', $config->getFilterDeleted());
        $this->assertSame('all', $config->getFilterFreshness());
        $this->assertSame([], $config->getFilterIds());
        $this->assertSame('all', $config->getFilterPriority());
        $this->assertSame(0, $config->getFilterSection());
        $this->assertFalse($config->getShowInactive());
        $this->assertSame('n', $config->getShowPriority());
        $this->assertFalse($config->getShowPriorityEditor());
    }

    /**
     * Verifies that jsonSerialize returns exactly the saved fields.
     *
     * @return void
     */
    public function testJsonSerialize(): void
    {
        $config = new DisplayConfig();
        $config->setFilterIds([1, 2]);
        $config->setShowPriorityEditor(true);

        $data = $config->jsonSerialize();

        $this->assertArrayHasKey('filterAging', $data);
        $this->assertArrayHasKey('filterClosed', $data);
        $this->assertArrayHasKey('filterDeleted', $data);
        $this->assertArrayHasKey('filterFreshness', $data);
        $this->assertArrayHasKey('filterPriority', $data);
        $this->assertArrayHasKey('filterSection', $data);
        $this->assertArrayHasKey('showInactive', $data);
        $this->assertArrayHasKey('showPriority', $data);

        $this->assertArrayNotHasKey('filterIds', $data);
        $this->assertArrayNotHasKey('showPriorityEditor', $data);
    }

    /**
     * Verifies that processRequest maps underscore query params to camelCase fields.
     *
     * @return void
     */
    public function testProcessRequest(): void
    {
        $request = Request::create('/', 'GET', [
            'filter_aging'     => '60',
            'filter_closed'    => 'today',
            'filter_deleted'   => 'none',
            'filter_freshness' => 'month',
            'filter_priority'  => 'normal',
            'filter_section'   => '3',
            'show_inactive'    => 'y',
            'show_priority'    => 'above_normal',
        ]);

        $config = (new DisplayConfig())->processRequest($request);

        $this->assertSame('60', $config->getFilterAging());
        $this->assertSame('today', $config->getFilterClosed());
        $this->assertSame('none', $config->getFilterDeleted());
        $this->assertSame('month', $config->getFilterFreshness());
        $this->assertSame('normal', $config->getFilterPriority());
        $this->assertSame(3, $config->getFilterSection());
        $this->assertTrue($config->getShowInactive());
        $this->assertSame('above_normal', $config->getShowPriority());
    }

    /**
     * Verifies that processRequest ignores unrecognized query params.
     *
     * @return void
     */
    public function testProcessRequestIgnoresUnknownParams(): void
    {
        $request = Request::create('/', 'GET', ['bogus_param' => 'value']);
        $config  = (new DisplayConfig())->processRequest($request);

        $this->assertSame('all', $config->getFilterAging());
    }

    /**
     * Verifies that processRequest returns the same DisplayConfig instance (fluent interface).
     *
     * @return void
     */
    public function testProcessRequestReturnsSelf(): void
    {
        $config  = new DisplayConfig();
        $request = Request::create('/');
        $result  = $config->processRequest($request);

        $this->assertSame($config, $result);
    }

    /**
     * Verifies that setFilterAging rejects invalid values.
     *
     * @return void
     */
    public function testSetFilterAgingInvalid(): void
    {
        $this->expectException(Exception::class);
        (new DisplayConfig())->setFilterAging('invalid');
    }

    /**
     * Data provider for testSetFilterAgingValid.
     *
     * @return array
     */
    public static function validFilterAgingProvider(): array
    {
        return [
            'all' => ['all'],
            '30'  => ['30'],
            '60'  => ['60'],
            '90'  => ['90'],
            '365' => ['365'],
        ];
    }

    /**
     * Verifies that setFilterAging accepts all valid values.
     *
     * @param string $value The valid aging filter value to test.
     *
     * @return void
     */
    #[DataProvider('validFilterAgingProvider')]
    public function testSetFilterAgingValid(string $value): void
    {
        $config = new DisplayConfig();
        $result = $config->setFilterAging($value);

        $this->assertSame($value, $config->getFilterAging());
        $this->assertSame($config, $result);
    }

    /**
     * Verifies that setFilterClosed rejects invalid values.
     *
     * @return void
     */
    public function testSetFilterClosedInvalid(): void
    {
        $this->expectException(Exception::class);
        (new DisplayConfig())->setFilterClosed('invalid');
    }

    /**
     * Data provider for testSetFilterClosedValid.
     *
     * @return array<string, array{string}>
     */
    public static function validFilterClosedProvider(): array
    {
        return [
            'none'     => ['none'],
            'recently' => ['recently'],
            'today'    => ['today'],
            'all'      => ['all'],
        ];
    }

    /**
     * Verifies that setFilterClosed accepts all valid values.
     *
     * @param string $value The valid closed filter value to test.
     *
     * @return void
     */
    #[DataProvider('validFilterClosedProvider')]
    public function testSetFilterClosedValid(string $value): void
    {
        $config = new DisplayConfig();
        $result = $config->setFilterClosed($value);

        $this->assertSame($value, $config->getFilterClosed());
        $this->assertSame($config, $result);
    }

    /**
     * Verifies that setFilterDeleted rejects invalid values.
     *
     * @return void
     */
    public function testSetFilterDeletedInvalid(): void
    {
        $this->expectException(Exception::class);
        (new DisplayConfig())->setFilterDeleted('invalid');
    }

    /**
     * Data provider for testSetFilterDeletedValid.
     *
     * @return array<string, array{string}>
     */
    public static function validFilterDeletedProvider(): array
    {
        return [
            'none'     => ['none'],
            'recently' => ['recently'],
            'today'    => ['today'],
            'all'      => ['all'],
        ];
    }

    /**
     * Verifies that setFilterDeleted accepts all valid values.
     *
     * @param string $value The valid deleted filter value to test.
     *
     * @return void
     */
    #[DataProvider('validFilterDeletedProvider')]
    public function testSetFilterDeletedValid(string $value): void
    {
        $config = new DisplayConfig();
        $result = $config->setFilterDeleted($value);

        $this->assertSame($value, $config->getFilterDeleted());
        $this->assertSame($config, $result);
    }

    /**
     * Verifies that setFilterFreshness rejects invalid values.
     *
     * @return void
     */
    public function testSetFilterFreshnessInvalid(): void
    {
        $this->expectException(Exception::class);
        (new DisplayConfig())->setFilterFreshness('invalid');
    }

    /**
     * Data provider for testSetFilterFreshnessValid.
     *
     * @return array<string, array{string}>
     */
    public static function validFilterFreshnessProvider(): array
    {
        return [
            'all'      => ['all'],
            'today'    => ['today'],
            'recently' => ['recently'],
            'week'     => ['week'],
            'month'    => ['month'],
        ];
    }

    /**
     * Verifies that setFilterFreshness accepts all valid values.
     *
     * @param string $value The valid freshness filter value to test.
     *
     * @return void
     */
    #[DataProvider('validFilterFreshnessProvider')]
    public function testSetFilterFreshnessValid(string $value): void
    {
        $config = new DisplayConfig();
        $result = $config->setFilterFreshness($value);

        $this->assertSame($value, $config->getFilterFreshness());
        $this->assertSame($config, $result);
    }

    /**
     * Verifies that setFilterIds stores the array and returns self.
     *
     * @return void
     */
    public function testSetFilterIds(): void
    {
        $config = new DisplayConfig();
        $ids    = [1, 5, 99];
        $result = $config->setFilterIds($ids);

        $this->assertSame($ids, $config->getFilterIds());
        $this->assertSame($config, $result);
    }

    /**
     * Verifies that setFilterPriority rejects invalid values.
     *
     * @return void
     */
    public function testSetFilterPriorityInvalid(): void
    {
        $this->expectException(Exception::class);
        (new DisplayConfig())->setFilterPriority('invalid');
    }

    /**
     * Data provider for testSetFilterPriorityValid.
     *
     * @return array<string, array{string}>
     */
    public static function validFilterPriorityProvider(): array
    {
        return [
            'all'    => ['all'],
            'high'   => ['high'],
            'normal' => ['normal'],
            'low'    => ['low'],
        ];
    }

    /**
     * Verifies that setFilterPriority accepts all valid values.
     *
     * @param string $value The valid priority filter value to test.
     *
     * @return void
     */
    #[DataProvider('validFilterPriorityProvider')]
    public function testSetFilterPriorityValid(string $value): void
    {
        $config = new DisplayConfig();
        $result = $config->setFilterPriority($value);

        $this->assertSame($value, $config->getFilterPriority());
        $this->assertSame($config, $result);
    }

    /**
     * Data provider for testSetFilterSection.
     *
     * @return array<string, array{mixed, int}>
     */
    public static function filterSectionProvider(): array
    {
        return [
            'integer'        => [5, 5],
            'string integer' => ['12', 12],
            'zero'           => [0, 0],
            'string zero'    => ['0', 0],
        ];
    }

    /**
     * Verifies that setFilterSection casts its argument to int and returns self.
     *
     * @param mixed $input    The input value to set.
     * @param int   $expected The expected integer result.
     *
     * @return void
     */
    #[DataProvider('filterSectionProvider')]
    public function testSetFilterSection(mixed $input, int $expected): void
    {
        $config = new DisplayConfig();
        $result = $config->setFilterSection($input);

        $this->assertSame($expected, $config->getFilterSection());
        $this->assertSame($config, $result);
    }

    /**
     * Data provider for testSetShowInactive.
     *
     * @return array<string, array{bool|string, bool}>
     */
    public static function showInactiveProvider(): array
    {
        return [
            'bool true'    => [true, true],
            'bool false'   => [false, false],
            'string y'     => ['y', true],
            'string n'     => ['n', false],
            'string other' => ['yes', false],
        ];
    }

    /**
     * Verifies that setShowInactive handles both bool and string inputs.
     *
     * @param bool|string $input    The input value to set.
     * @param bool        $expected The expected boolean result.
     *
     * @return void
     */
    #[DataProvider('showInactiveProvider')]
    public function testSetShowInactive(bool|string $input, bool $expected): void
    {
        $config = new DisplayConfig();
        $result = $config->setShowInactive($input);

        $this->assertSame($expected, $config->getShowInactive());
        $this->assertSame($config, $result);
    }

    /**
     * Data provider for testSetShowPriorityEditor.
     *
     * @return array<string, array{bool|string, bool}>
     */
    public static function showPriorityEditorProvider(): array
    {
        return [
            'bool true'    => [true, true],
            'bool false'   => [false, false],
            'string y'     => ['y', true],
            'string n'     => ['n', false],
            'string other' => ['yes', false],
        ];
    }

    /**
     * Verifies that setShowPriorityEditor handles both bool and string inputs.
     *
     * @param bool|string $input    The input value to set.
     * @param bool        $expected The expected boolean result.
     *
     * @return void
     */
    #[DataProvider('showPriorityEditorProvider')]
    public function testSetShowPriorityEditor(bool|string $input, bool $expected): void
    {
        $config = new DisplayConfig();
        $result = $config->setShowPriorityEditor($input);

        $this->assertSame($expected, $config->getShowPriorityEditor());
        $this->assertSame($config, $result);
    }

    /**
     * Verifies that setShowPriority rejects invalid values.
     *
     * @return void
     */
    public function testSetShowPriorityInvalid(): void
    {
        $this->expectException(Exception::class);
        (new DisplayConfig())->setShowPriority('invalid');
    }

    /**
     * Data provider for testSetShowPriorityValid.
     *
     * @return array<string, array{string}>
     */
    public static function validShowPriorityProvider(): array
    {
        return [
            'y'            => ['y'],
            'above_normal' => ['above_normal'],
            'n'            => ['n'],
        ];
    }

    /**
     * Verifies that setShowPriority accepts all valid values.
     *
     * @param string $value The valid show priority value to test.
     *
     * @return void
     */
    #[DataProvider('validShowPriorityProvider')]
    public function testSetShowPriorityValid(string $value): void
    {
        $config = new DisplayConfig();
        $result = $config->setShowPriority($value);

        $this->assertSame($value, $config->getShowPriority());
        $this->assertSame($config, $result);
    }

    /**
     * Verifies that __sleep returns only the saved field names.
     *
     * @return void
     */
    public function testSleep(): void
    {
        $config = new DisplayConfig();
        $fields = $config->__sleep();

        $this->assertContains('filterAging', $fields);
        $this->assertContains('filterClosed', $fields);
        $this->assertContains('filterDeleted', $fields);
        $this->assertContains('filterFreshness', $fields);
        $this->assertContains('filterPriority', $fields);
        $this->assertContains('filterSection', $fields);
        $this->assertContains('showInactive', $fields);
        $this->assertContains('showPriority', $fields);

        $this->assertNotContains('filterIds', $fields);
        $this->assertNotContains('showPriorityEditor', $fields);
    }
}
