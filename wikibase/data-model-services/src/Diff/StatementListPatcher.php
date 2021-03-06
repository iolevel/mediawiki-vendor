<?php

namespace Wikibase\DataModel\Services\Diff;

use Diff\DiffOp\Diff\Diff;
use Diff\DiffOp\DiffOp;
use Diff\DiffOp\DiffOpAdd;
use Diff\DiffOp\DiffOpChange;
use Diff\DiffOp\DiffOpRemove;
use Diff\Patcher\PatcherException;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @since 3.6
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Kreuz
 */
class StatementListPatcher {

	/**
	 * @since 3.6
	 *
	 * @param StatementList $statements
	 * @param Diff $patch
	 *
	 * @throws PatcherException
	 */
	public function patchStatementList( StatementList $statements, Diff $patch ) {
		/** @var DiffOp $diffOp */
		foreach ( $patch as $diffOp ) {
			switch ( true ) {
				case $diffOp instanceof DiffOpAdd:
					/** @var DiffOpAdd $diffOp */
					/** @var Statement $statement */
					$statement = $diffOp->getNewValue();
					$guid = $statement->getGuid();
					if ( $statements->getFirstStatementWithGuid( $guid ) === null ) {
						$statements->addStatement( $statement );
					}
					break;

				case $diffOp instanceof DiffOpChange:
					/** @var DiffOpChange $diffOp */
					/** @var Statement $oldStatement */
					/** @var Statement $newStatement */
					$oldStatement = $diffOp->getOldValue();
					$newStatement = $diffOp->getNewValue();
					$this->changeStatement( $statements, $oldStatement->getGuid(), $newStatement );
					break;

				case $diffOp instanceof DiffOpRemove:
					/** @var DiffOpRemove $diffOp */
					/** @var Statement $statement */
					$statement = $diffOp->getOldValue();
					$statements->removeStatementsWithGuid( $statement->getGuid() );
					break;

				default:
					throw new PatcherException( 'Invalid statement list diff' );
			}
		}
	}

	/**
	 * @param StatementList $statements
	 * @param string|null $oldGuid
	 * @param Statement $newStatement
	 */
	private function changeStatement( StatementList $statements, $oldGuid, Statement $newStatement ) {
		foreach ( $statements->toArray() as $index => $statement ) {
			if ( $statement->getGuid() === $oldGuid ) {
				$statements->removeStatementsWithGuid( $oldGuid );
				$statements->addStatement( $newStatement, $index );
				return;
			}
		}
	}

}
