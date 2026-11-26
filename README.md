<p align=center><img src="./resources/kw-logo.svg" width=500></p>

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg?style=flat-square)](https://php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://github.com/Kruithne/KrameWork/blob/master/LICENSE)
[![Tests](https://img.shields.io/github/actions/workflow/status/Kruithne/KrameWork/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/Kruithne/KrameWork/actions/workflows/tests.yml)

KrameWork is an OOP PHP toolkit for building websites and scripts. It provides independent components for auto-loading, dependency injection, databases, caching, HTTP, MVC, mailing, error reporting and security. You can use only the parts you need, with no framework configuration or bootstrap. For example, to get auto-loading in a script, create a new `AutoLoader` instance.

- **Core**
	- [AutoLoader](docs/AutoLoader.md)
	- [DependencyInjector](docs/DependencyInjector.md)
- **API**
	- [reCAPTCHA](docs/API/reCAPTCHA.md)
- **Caching**
	- [APCu](docs/Caching/APCu.md)
	- [FileCache](docs/Caching/FileCache.md)
	- [Memcached](docs/Caching/Memcached.md)
	- [Session](docs/Caching/Session.md)
- **Data**
	- [Value](docs/Data/Value.md)
	- [CurrencyValue](docs/Data/CurrencyValue.md)
	- [DateTimeValue](docs/Data/DateTimeValue.md)
	- [DateValue](docs/Data/DateValue.md)
	- [DecimalValue](docs/Data/DecimalValue.md)
	- [IntegerValue](docs/Data/IntegerValue.md)
	- [StringValue](docs/Data/StringValue.md)
- **Database**
	- [Database](docs/Database/Database.md)
	- [ConnectionString](docs/Database/ConnectionString.md)
	- [DatabaseCache](docs/Database/DatabaseCache.md)
	- [DatabaseManager](docs/Database/DatabaseManager.md)
	- [IDatabaseManager](docs/Database/IDatabaseManager.md)
	- [EncryptedConnection](docs/Database/EncryptedConnection.md)
	- [MonitoredDatabase](docs/Database/MonitoredDatabase.md)
	- [ManagedTable](docs/Database/ManagedTable.md)
	- [TableVersion](docs/Database/TableVersion.md)
- **HTTP**
	- [HTTPContext](docs/HTTP/HTTPContext.md)
	- [HTTPHeaders](docs/HTTP/HTTPHeaders.md)
	- [CSPHeader](docs/HTTP/CSPHeader.md)
	- [HSTSHeader](docs/HTTP/HSTSHeader.md)
	- [MIMEValidationHeader](docs/HTTP/MIMEValidationHeader.md)
	- [ReferrerPolicyHeader](docs/HTTP/ReferrerPolicyHeader.md)
	- [XFrameHeader](docs/HTTP/XFrameHeader.md)
	- [XSSProtectionHeader](docs/HTTP/XSSProtectionHeader.md)
	- [WebRequest](docs/HTTP/Request/WebRequest.md)
	- [JSONRequest](docs/HTTP/Request/JSONRequest.md)
- **Mailing**
	- [Mail](docs/Mailing/Mail.md)
	- [AttachmentFile](docs/Mailing/AttachmentFile.md)
	- [EncodedContent](docs/Mailing/EncodedContent.md)
	- [RecipientCollection](docs/Mailing/RecipientCollection.md)
	- [SMTPTransport](docs/Mailing/SMTPTransport.md)
- **MVC**
	- [Module](docs/MVC/Module.md)
	- [Template](docs/MVC/Template.md)
- **Reporting**
	- [ReportRunner](docs/Reporting/ReportRunner.md)
	- [SQLReportRunner](docs/Reporting/SQLReportRunner.md)
	- [ReportResults](docs/Reporting/ReportResults.md)
	- [ReportRow](docs/Reporting/ReportRow.md)
	- [ReportColumn](docs/Reporting/ReportColumn.md)
	- [HTMLReportTemplate](docs/Reporting/HTMLReport/HTMLReportTemplate.md)
	- [HTMLReportSection](docs/Reporting/HTMLReport/HTMLReportSection.md)
- **Error Handling**
	- [ErrorHandler](docs/Runtime/ErrorHandler.md)
- **Error Dispatchers**
	- [BufferDispatcher](docs/Runtime/ErrorDispatchers/BufferDispatcher.md)
	- [FileDispatcher](docs/Runtime/ErrorDispatchers/FileDispatcher.md)
	- [GitHubDispatcher](docs/Runtime/ErrorDispatchers/GitHubDispatcher.md)
	- [MailDispatcher](docs/Runtime/ErrorDispatchers/MailDispatcher.md)
- **Error Formatters**
	- [HTMLErrorFormatter](docs/Runtime/ErrorFormatters/HTMLErrorFormatter.md)
	- [JSONErrorFormatter](docs/Runtime/ErrorFormatters/JSONErrorFormatter.md)
	- [MarkdownErrorFormatter](docs/Runtime/ErrorFormatters/MarkdownErrorFormatter.md)
	- [PlainTextErrorFormatter](docs/Runtime/ErrorFormatters/PlainTextErrorFormatter.md)
- **Security**
	- [Password](docs/Security/Password.md)
- **Storage**
	- [File](docs/Storage/File.md)
	- [Directory](docs/Storage/Directory.md)
	- [JSONFile](docs/Storage/JSONFile.md)
	- [MemoryFile](docs/Storage/MemoryFile.md)
	- [UploadedFile](docs/Storage/UploadedFile.md)
- **Timing**
	- [Time](docs/Timing/Time.md)
	- [Timer](docs/Timing/Timer.md)
	- [Benchmark](docs/Timing/Benchmarking/Benchmark.md)
	- [BenchmarkReport](docs/Timing/Benchmarking/BenchmarkReport.md)
- **Users**
	- [Getting Started](docs/Users/GettingStarted.md)
	- [UserManager](docs/Users/UserManager.md)
	- [UserSessionManager](docs/Users/UserSessionManager.md)
	- [UserSession](docs/Users/UserSession.md)
	- [UserAccountFlags](docs/Users/UserAccountFlags.md)
	- [IUserMailer](docs/Users/IUserMailer.md)
	- [OAuthManager](docs/Users/OAuthManager.md)
	- [OAuthProvider](docs/Users/OAuthProvider.md)
	- [UserTables](docs/Users/UserTables.md)
- **Utilities**
	- [StringBuilder](docs/Utils/StringBuilder.md)
	- [StringUtil](docs/Utils/StringUtil.md)
	- [UUID](docs/Utils/UUID.md)

## Disclaimer
This is a personal project, intended for use on other personal projects. I make no promises about the performance or reliability of the framework, however it is in my best interest to make it as solid as possible. If you find any issues or room for improvement, feel free to contribute.
