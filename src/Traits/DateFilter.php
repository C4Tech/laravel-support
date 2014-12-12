<?php namespace C4tech\Foundation\Traits;

use Carbon\Carbon;

/**
 * Date Filter Trait
 *
 * Common methods for filtering date properties.
 */
trait DateFilter
{
    /**
     * Scope: Created Before
     *
     * Reusable method to filter query by Campaign start date.
     * @param  Carbon $date Comparison reference
     */
    public function scopeCreatedBefore($query, $date = 'now')
    {
        return $this->whenCreated($query, $date, '<');
    }

    /**
     * Scope: Created Before
     *
     * Reusable method to filter query by Campaign start date.
     * @param  Carbon $date Comparison reference
     */
    public function scopeCreatedOnOrBefore($query, $date = 'now')
    {
        return $this->whenCreated($query, $date, '<=');
    }

    /**
     * Scope: Created After
     *
     * Reusable method to filter query by Campaign start date.
     * @param  Carbon $date Comparison reference
     */
    public function scopeCreatedOnOrAfter($query, $date = 'now')
    {
        return $this->whenCreated($query, $date, '>=');
    }

    /**
     * Scope: Created After
     *
     * Reusable method to filter query by Campaign start date.
     * @param  Carbon $date Comparison reference
     */
    public function scopeCreatedAfter($query, $date = 'now')
    {
        return $this->whenCreated($query, $date, '>');
    }

    /**
     * Scope: Updated Before
     *
     * Reusable method to filter query by Campaign end date.
     * @param  Carbon $date Comparison reference
     */
    public function scopeUpdatedBefore($query, $date = 'now')
    {
        return $this->whenUpdated($query, $date, '<');
    }

    /**
     * Scope: Updated Before
     *
     * Reusable method to filter query by Campaign end date.
     * @param  Carbon $date Comparison reference
     */
    public function scopeUpdatedOnOrBefore($query, $date = 'now')
    {
        return $this->whenUpdated($query, $date, '<=');
    }

    /**
     * Scope: Updated When
     *
     * Reusable method to filter query by Campaign end date.
     * @param  Carbon $date Comparison reference
     */
    public function scopeUpdatedOnOrAfter($query, $date = 'now')
    {
        return $this->whenUpdated($query, $date, '>=');
    }

    /**
     * Scope: Updated When
     *
     * Reusable method to filter query by Campaign end date.
     * @param  Carbon $date Comparison reference
     */
    public function scopeUpdatedAfter($query, $date = 'now')
    {
        return $this->whenUpdated($query, $date, '>');
    }

    /**
     * Protected Scope: Created When
     *
     * Reusable method to filter query by Campaign start date.
     * @param  Carbon $date Comparison reference
     * @param  string $comp Comparison value (default is >=)
     */
    protected function whenCreated($query, $date = 'now', $comp = '>=')
    {
        return $this->whenOn($query, 'created_at', $date, $comp);
    }


    /**
     * Protected Scope: Updated When
     *
     * Reusable method to filter query by Campaign end date.
     * @param  Carbon $date Comparison reference
     * @param  string $comp Comparison value (default is >=)
     */
    protected function whenUpdated($query, $date = 'now', $comp = '>=')
    {
        return $this->whenOn($query, 'updated_at', $date, $comp);
    }

    /**
     * Protected Scope: When On
     *
     * Reusable method to filter query by a date/time field.
     * @param  string $field Name of DB date/time field to use
     * @param  mixed  $date  Comparison reference
     * @param  string $comp  Comparison value (default is >=)
     */
    protected function whenOn($query, $field, $date = 'now', $comp = '>=')
    {
        return $query->where($field, $comp, Carbon::parse($date)->toDateString());
    }
}
