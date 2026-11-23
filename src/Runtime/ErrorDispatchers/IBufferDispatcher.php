<?php
	namespace KrameWork\Runtime\ErrorDispatchers;
	use KrameWork\Runtime\ErrorReports\IErrorReport;

	/**
	 * Interface IErrorDispatcher
	 * Represents classes in charge of dispatching error reports via writing to the PHP output buffer.
	 * This is used to tag dispatchers that cannot be used during core error handling.
	 *
	 * @package KrameWork\Runtime\ErrorDispatchers
	 * @author docpify <morten@runsafe.no>
	 */
	interface IBufferDispatcher
	{
	}