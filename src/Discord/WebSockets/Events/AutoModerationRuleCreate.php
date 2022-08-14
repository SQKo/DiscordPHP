<?php

/*
 * This file is a part of the DiscordPHP project.
 *
 * Copyright (c) 2015-present David Cole <david.cole1340@gmail.com>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

namespace Discord\WebSockets\Events;

use Discord\WebSockets\Event;
use Discord\Helpers\Deferred;
use Discord\Parts\Guild\AutoModeration\Rule;
use Discord\Parts\Guild\Guild;

use function React\Async\coroutine;

/**
 * @see https://discord.com/developers/docs/topics/gateway#auto-moderation-rule-create
 */
class AutoModerationRuleCreate extends Event
{
    /**
     * @inheritdoc
     */
    public function handle(Deferred &$deferred, $data): void
    {
        coroutine(function ($data) {
            $rulePart = null;

            /** @var ?Guild */
            if ($guild = yield $this->discord->guilds->cacheGet($data->guild_id)) {
                /** @var ?Rule */
                if ($rulePart = $guild->auto_moderation_rules[$data->id]) {
                    $rulePart->fill((array) $data);
                }
            }

            if ($rulePart === null) {
                /** @var Rule */
                $rulePart = $this->factory->create(Rule::class, $data, true);
            }

            if ($guild) {
                yield $guild->auto_moderation_rules->cache->set($data->id, $rulePart);
            }

            return $rulePart;
        }, $data)->then([$deferred, 'resolve']);
    }
}
