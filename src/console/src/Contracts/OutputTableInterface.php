<?php

declare(strict_types=1);

namespace Larmias\Console\Contracts;

interface OutputTableInterface
{
    /**
     * @var int
     */
    public const ALIGN_LEFT = 1;

    /**
     * @var int
     */
    public const ALIGN_RIGHT = 0;

    /**
     * @var int
     */
    public const ALIGN_CENTER = 2;

    /**
     * 设置表格头信息 以及对齐方式
     *
     * @param array $header
     * @param int $align
     * @return OutputTableInterface
     */
    public function setHeader(array $header, int $align = OutputTableInterface::ALIGN_LEFT): OutputTableInterface;

    /**
     * 设置输出表格数据 及对齐方式
     *
     * @param array $rows
     * @param int $align
     * @return OutputTableInterface
     */
    public function setRows(array $rows, int $align = OutputTableInterface::ALIGN_LEFT): OutputTableInterface;

    /**
     * 设置全局单元格对齐方式
     *
     * @param int $align
     * @return OutputTableInterface
     */
    public function setCellAlign(int $align = OutputTableInterface::ALIGN_LEFT): OutputTableInterface;

    /**
     * 增加一行表格数据
     *
     * @param mixed $row 行数据
     * @param bool $first 是否在开头插入
     * @return OutputTableInterface
     */
    public function addRow(mixed $row, bool $first = false): OutputTableInterface;

    /**
     * 设置输出表格的样式
     *
     * @param string $style
     * @return OutputTableInterface
     */
    public function setStyle(string $style): OutputTableInterface;

    /**
     * 输出表格
     *
     * @param array $dataList
     * @return string
     */
    public function render(array $dataList = []): string;
}