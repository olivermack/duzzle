Duzzle: An opinionated, DTO-centric Guzzle HTTP Wrapper
===

Duzzle (_[DTOs](https://en.wikipedia.org/wiki/Data_transfer_object) + [Guzzle](https://github.com/guzzle/guzzle)_) is a lightweight extension on top of Guzzle designed to seamlessly integrate DTO serialization and validation workflows into your HTTP client calls. 
It leverages the Symfony Serializer and Validator to transform your domain objects (DTOs) into request payloads, validate them before dispatch, and then deserialize responses back into strongly typed objects—enabling a clean, high-level API around Guzzle’s powerful HTTP capabilities. 

If you’re seeking a straightforward, “DTO-first” approach to RESTful interactions without manually handling JSON or validation rules, Duzzle aims to provide an easy and extensible solution.

## Todos

- [x] dev ecosystem 
- [x] lib builder / factories 
- [x] most crucial code quality tooling
- [ ] DTO (de)serialization
  - [x] dealing with JSON API output
  - [x] dealing with JSON API input
  - [ ] dealing with XML API output
  - [ ] dealing with XML API input
- [x] DTO validation
  - [x] validating output DTOs
  - [x] validating input DTOs
