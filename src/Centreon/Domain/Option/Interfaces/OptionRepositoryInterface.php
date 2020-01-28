<?php
/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Centreon\Domain\Option\Interfaces;

use Centreon\Domain\Option\Option;

interface OptionRepositoryInterface
{
    /**
     * Find all options.
     *
     * @param bool $useCache Indicates whether we should use the cache system or not (TRUE by default)
     * @return Option[] Returns all available options
     */
    public function findAllOptions(bool $useCache = true): array;
}