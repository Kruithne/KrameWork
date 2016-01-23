<?php
	interface ICRUDService extends ICRUD
	{
		/**
		 * Gets the value of the Access-Control-Allow-Origin header
		 * @return string A URL or wildcard for access control
		 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS CORS documentation
		 */
		public function getOrigin();
		/**
		 * Gets the value of the Access-Control-Allow-Methods header
		 * @return string A list of methods to allow the client to use with this service
		 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS CORS documentation
		 */
		public function getMethod();

		/**
		 * Entry point to run the service
		 */
		public function execute();
		/**
		 * Check if the current request is authorized
		 * @param object $request The JSON object posted to the service
		 * @return bool Service access is valid
		 */
		public function authorized($request);
		/**
		 * Check if the current request is authorized to create the given object
		 * @param object $object The JSON object posted to the service
		 * @return bool Object creation is valid
		 */
		public function canCreate($object);
		/**
		 * Check if the current request is authorized to read objects
		 * @return bool Object read access is valid
		 */
		public function canRead();
		/**
		 * Check if the current request is authorized to save the given object
		 * @param object $object The JSON object posted to the service
		 * @return bool Object modification is valid
		 */
		public function canUpdate($object);
		/**
		 * Check if the current request is authorized to delete the given object
		 * @param object $object The JSON object posted to the service
		 * @return bool Object deletion is valid
		 */
		public function canDelete($object);
		/**
		 * Process a service request. Override this to add custom methods
		 * @param object $object The JSON object posted to the service
		 */
		public function process($object);
	}
?>
