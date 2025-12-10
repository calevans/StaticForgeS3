# [StaticForge](https://calevans.com/staticforge) S3MediaOffload

A StaticForge feature package.

Copyright (c) 2025 Cal Evans <br />
License: MIT

## Installation

```bash
composer require calevans/staticforge-s3
php vendor/bin/staticforge feature:setup S3MediaOffload
```

## Configuration

Add the following to your project's `.env` file:

```dotenv
S3_BUCKET="your-s3-bucket-name"
S3_REGION="us-east-1"
S3_ACCESS_KEY="your-access-key-id"
S3_SECRET_KEY="your-secret-access-key"
# Optional
# S3_ENDPOINT="https://nyc3.digitaloceanspaces.com"
```
