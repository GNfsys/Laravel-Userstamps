<?php

namespace Mattiverse\Userstamps\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Mattiverse\Userstamps\UserstampsScope;

trait Userstamps
{
    protected bool $userstamping = true;

    public static function bootUserstamps(): void
    {
        static::addGlobalScope(new UserstampsScope);

        static::registerListeners();
    }

    public static function registerListeners(): void
    {
        static::creating('Mattiverse\Userstamps\Listeners\Creating@handle');
        static::updating('Mattiverse\Userstamps\Listeners\Updating@handle');

        if (static::usingSoftDeletes()) {
            static::deleting('Mattiverse\Userstamps\Listeners\Deleting@handle');
            static::restoring('Mattiverse\Userstamps\Listeners\Restoring@handle');
        }
    }

    public static function usingSoftDeletes(): bool
    {
        static $usingSoftDeletes;

        if (is_null($usingSoftDeletes)) {
            return $usingSoftDeletes = in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(get_called_class()));
        }

        return $usingSoftDeletes;
    }

    /**
     * @template TRelatedModel of Authenticatable
     *
     * @return BelongsTo<Authenticatable, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo($this->getUserClass(), $this->getCreatedByColumn());
    }

    /**
     * @template TRelatedModel of Authenticatable
     *
     * @return BelongsTo<TRelatedModel, $this>
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo($this->getUserClass(), $this->getUpdatedByColumn());
    }

    /**
     * @template TRelatedModel of Authenticatable
     *
     * @return BelongsTo<TRelatedModel, $this>
     */
    public function destroyer(): BelongsTo
    {
        return $this->belongsTo($this->getUserClass(), $this->getDeletedByColumn());
    }

    public function getCreatedByColumn(): ?string
    {
        return defined('static::CREATED_BY') ? constant(static::class.'::CREATED_BY') : 'created_by';
    }

    public function getUpdatedByColumn(): ?string
    {
        return defined('static::UPDATED_BY') ? constant(static::class.'::UPDATED_BY') : 'updated_by';
    }

    public function getDeletedByColumn(): ?string
    {
        return defined('static::DELETED_BY') ? constant(static::class.'::DELETED_BY') : 'deleted_by';
    }

    public function isUserstamping(): bool
    {
        return $this->userstamping;
    }

    public function stopUserstamping(): void
    {
        $this->userstamping = false;
    }

    public function startUserstamping(): void
    {
        $this->userstamping = true;
    }

    protected function getUserClass(): string
    {
        return config('auth.providers.users.model');
    }
}
