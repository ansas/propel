<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Generator\Behavior\Timestampable;

use Propel\Generator\Builder\Om\AbstractOMBuilder;
use Propel\Generator\Model\Behavior;

/**
 * Gives a model class the ability to track creation and last modification dates
 * Uses two additional columns storing the creation and update date
 *
 * @author François Zaninotto
 * @author Ansas Meyer
 */
class TimestampableBehavior extends Behavior
{
    /**
     * @var array<string, mixed>
     */
    protected $parameters = [
        'create_column'         => 'created_at',
        'update_column'         => 'updated_at',
        'disable_created_at'    => 'false',
        'disable_updated_at'    => 'false',
        'enable_high_precision' => 'false',
        'date_type'             => 'DATETIME',
    ];

    /**
     * @return bool
     */
    protected function withUpdatedAt(): bool
    {
        return !$this->booleanValue($this->getParameter('disable_updated_at'));
    }

    /**
     * @return bool
     */
    protected function withCreatedAt(): bool
    {
        return !$this->booleanValue($this->getParameter('disable_created_at'));
    }

    protected function withHighPrecision(): bool
    {
        return $this->booleanValue($this->getParameter('enable_high_precision'));
    }

    /**
     * Add the create_column and update_columns to the current table
     *
     * @return void
     */
    public function modifyTable(): void
    {
        $table = $this->getTable();

        if ($this->withCreatedAt() && !$table->hasColumn($this->getParameter('create_column'))) {
            $table->addColumn([
                'name' => $this->getParameter('create_column'),
                'type' => $this->getParameter('date_type'),
            ]);
        }
        if ($this->withUpdatedAt() && !$table->hasColumn($this->getParameter('update_column'))) {
            $table->addColumn([
                'name' => $this->getParameter('update_column'),
                'type' => $this->getParameter('date_type'),
            ]);
        }
    }

    /**
     * @return string the PHP code to be added to the builder
     */
    public function objectAttributes($builder)
    {
        if (!$this->withUpdatedAt()) {
            return '';
        }

        return "protected \$keepUpdateDateUnchanged = false;\n";
    }

    /**
     * Get the setter of one of the columns of the behavior
     *
     * @param string $column One of the behavior columns, 'create_column' or 'update_column'
     *
     * @return string The related setter, 'setCreatedOn' or 'setUpdatedOn'
     */
    protected function getColumnSetter(string $column): string
    {
        return 'set' . $this->getColumnForParameter($column)->getPhpName();
    }

    /**
     * @param string $columnName
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     *
     * @return string
     */
    protected function getColumnConstant(string $columnName, AbstractOMBuilder $builder): string
    {
        return $builder->getColumnConstant($this->getColumnForParameter($columnName));
    }

    /**
     * Add code in ObjectBuilder::preUpdate
     *
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     *
     * @return string The code to put at the hook
     */
    public function preUpdate(AbstractOMBuilder $builder): string
    {
        if ($this->withUpdatedAt()) {
            $valueSource = strtoupper($this->getTable()->getColumn($this->getParameter('update_column'))->getType()) === 'INTEGER'
             || !$this->withHighPrecision()
                ? 'time()'
                : '\\Propel\\Runtime\\Util\\PropelDateTime::createHighPrecision()'
            ;
            return "if (\$this->isModified() && !\$this->keepUpdateDateUnchanged && !\$this->isColumnModified(" . $this->getColumnConstant('update_column', $builder) . ")) {
    \$this->" . $this->getColumnSetter('update_column') . "({$valueSource});
}";
        }

        return '';
    }

    /**
     * Add code in ObjectBuilder::preInsert
     *
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     *
     * @return string The code to put at the hook
     */
    public function preInsert(AbstractOMBuilder $builder): string
    {
        $script = '$time = time();
' .  ($this->withHighPrecision() ? '$highPrecision = \\Propel\\Runtime\\Util\\PropelDateTime::createHighPrecision();' : '');

        if ($this->withCreatedAt()) {
            $valueSource = strtoupper($this->getTable()->getColumn($this->getParameter('create_column'))->getType()) === 'INTEGER'
            || !$this->withHighPrecision()
                ? '$time'
                : '$highPrecision';
            $script .= "
if (!\$this->isColumnModified(" . $this->getColumnConstant('create_column', $builder) . ")) {
    \$this->" . $this->getColumnSetter('create_column') . "($valueSource);
}";
        }

        if ($this->withUpdatedAt()) {
            $valueSource = strtoupper($this->getTable()->getColumn($this->getParameter('update_column'))->getType()) === 'INTEGER'
            || !$this->withHighPrecision()
                ? '$time'
                : '$highPrecision';
            $script .= "
if (!\$this->isColumnModified(" . $this->getColumnConstant('update_column', $builder) . ")) {
    \$this->" . $this->getColumnSetter('update_column') . "($valueSource);
}";
        }

        return $script;
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     *
     * @return string
     */
    public function objectMethods(AbstractOMBuilder $builder): string
    {
        if (!$this->withUpdatedAt()) {
            return '';
        }

        return "
/**
 * Mark the current object so that the update date doesn't get updated during next save
 *
 * @param bool \$keep [optional]
 *
 * @return \$this The current object (for fluent API support)
 */
public function keepUpdateDateUnchanged(\$keep = true)
{
    \$this->keepUpdateDateUnchanged = (bool) \$keep;

    return \$this;
}
";
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     *
     * @return string
     */
    public function queryMethods(AbstractOMBuilder $builder): string
    {
        $script = '';

        if ($this->withUpdatedAt()) {
            $updateColumnConstant = $this->getColumnConstant('update_column', $builder);
            $script .= "
/**
 * Filter by the latest updated
 *
 * @param int \$nbDays Maximum age of the latest update in days
 *
 * @return \$this The current query, for fluid interface
 */
public function recentlyUpdated(\$nbDays = 7)
{
    \$this->addUsingAlias($updateColumnConstant, time() - \$nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);

    return \$this;
}

/**
 * Order by update date desc
 *
 * @return \$this The current query, for fluid interface
 */
public function lastUpdatedFirst()
{
    \$this->addDescendingOrderByColumn($updateColumnConstant);

    return \$this;
}

/**
 * Order by update date asc
 *
 * @return \$this The current query, for fluid interface
 */
public function firstUpdatedFirst()
{
    \$this->addAscendingOrderByColumn($updateColumnConstant);

    return \$this;
}
";
        }

        if ($this->withCreatedAt()) {
            $createColumnConstant = $this->getColumnConstant('create_column', $builder);
            $script .= "
/**
 * Order by create date desc
 *
 * @return \$this The current query, for fluid interface
 */
public function lastCreatedFirst()
{
    \$this->addDescendingOrderByColumn($createColumnConstant);

    return \$this;
}

/**
 * Filter by the latest created
 *
 * @param int \$nbDays Maximum age of in days
 *
 * @return \$this The current query, for fluid interface
 */
public function recentlyCreated(\$nbDays = 7)
{
    \$this->addUsingAlias($createColumnConstant, time() - \$nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);

    return \$this;
}

/**
 * Order by create date asc
 *
 * @return \$this The current query, for fluid interface
 */
public function firstCreatedFirst()
{
    \$this->addAscendingOrderByColumn($createColumnConstant);

    return \$this;
}
";
        }

        return $script;
    }
}
