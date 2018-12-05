<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace passport\classes;

use wulaphp\form\FormTable;

interface IOauth {
    public function setOptions(array $options);

    /**
     * @param array $data
     *
     * @return bool|array
     */
    public function check(array $data);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getDesc(): string;

    /**
     * @return \wulaphp\form\FormTable
     */
    public function getForm(): ?FormTable;

    public function supports(): array;

    public function getOauthData(?array $meta = null): array;
}