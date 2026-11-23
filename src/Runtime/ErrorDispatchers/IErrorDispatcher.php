<?php
	namespace KrameWork\Runtime\ErrorDispatchers;
	use KrameWork\Runtime\ErrorReports\IErrorReport;

	/**
	 * Interface IErrorDispatcher
	 * Represents classes in charge of dispatching error reports.
	 *
	 * @package KrameWork\Runtime\ErrorDispatchers
	 * @author Kruithne <kruithne@gmail.com>
	 */
	interface IErrorDispatcher
	{
		/**
		 * Dispatch an error report.
		 *
		 * @api dispatch
		 * @param IErrorReport|string $report Report to dispatch.
		 * @return bool Terminate script.
		 */
		public function dispatch($report):bool;
	}