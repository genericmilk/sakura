# Gemini Setup Guide for Robin

This guide will help you set up Google's Gemini AI provider for Robin, enabling you to generate tests using Google's powerful AI models.

## Prerequisites

- A Google account
- Access to Google AI Studio (MakerSuite)
- PHP 8.1+ with Composer
- Laravel project with Robin installed

## Step 1: Get a Google AI API Key

1. **Visit Google AI Studio:**

   - Go to [https://makersuite.google.com/app/apikey](https://makersuite.google.com/app/apikey)
   - Sign in with your Google account

2. **Create an API Key:**

   - Click "Create API Key"
   - Choose "Create API Key" from the dropdown
   - Copy the generated API key (it starts with `AIza...`)

3. **Enable the API:**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Enable the "Generative Language API" for your project

## Step 2: Configure Robin for Gemini

1. **Update your `.env` file:**

```env
ROBIN_AI_PROVIDER=gemini
GOOGLE_AI_API_KEY=AIzaSyYourApiKeyHere
ROBIN_GEMINI_MODEL=gemini-1.5-pro
ROBIN_GEMINI_MAX_TOKENS=4000
ROBIN_GEMINI_TEMPERATURE=0.3
ROBIN_GEMINI_TIMEOUT=60
```

2. **Publish the configuration (if not already done):**

```bash
php artisan vendor:publish --tag=robin-config
```

3. **Update `config/robin.php` if needed:**

```php
'gemini' => [
    'api_key' => env('GOOGLE_AI_API_KEY'),
    'model' => env('ROBIN_GEMINI_MODEL', 'gemini-1.5-pro'),
    'max_tokens' => env('ROBIN_GEMINI_MAX_TOKENS', 4000),
    'temperature' => env('ROBIN_GEMINI_TEMPERATURE', 0.3),
    'timeout' => env('ROBIN_GEMINI_TIMEOUT', 60),
],
```

## Step 3: Test Your Configuration

1. **Run a dry-run test:**

```bash
php artisan robin:generate-tests --provider=gemini --dry-run
```

2. **Check for any configuration errors:**

```bash
php artisan robin:generate-tests --provider=gemini --class=ExampleController
```

## Available Gemini Models

Robin supports the following Gemini models:

| Model                   | Description                               | Best For                             |
| ----------------------- | ----------------------------------------- | ------------------------------------ |
| `gemini-1.5-pro`        | Most capable model, best for complex code | Production use, complex applications |
| `gemini-1.5-flash`      | Fast and efficient, good balance          | Development, quick iterations        |
| `gemini-1.0-pro`        | Stable and reliable                       | General use, stable applications     |
| `gemini-1.0-pro-vision` | Supports image input                      | Code with visual components          |

### Recommended Model Selection

- **For most use cases**: `gemini-1.5-pro`
- **For faster development**: `gemini-1.5-flash`
- **For stability**: `gemini-1.0-pro`

## Usage Examples

### Basic Test Generation

```bash
# Generate tests for all changed code
php artisan robin:generate-tests --provider=gemini

# Generate tests for a specific class
php artisan robin:generate-tests --provider=gemini --class=UserController

# Generate tests for a specific function
php artisan robin:generate-tests --provider=gemini --function=calculateTotal
```

### Advanced Usage

```bash
# Force regeneration of all tests
php artisan robin:generate-tests --provider=gemini --force

# Dry run to see what would be generated
php artisan robin:generate-tests --provider=gemini --dry-run

# Override model for this run
ROBIN_GEMINI_MODEL=gemini-1.5-flash php artisan robin:generate-tests --provider=gemini
```

## Configuration Options

### Environment Variables

| Variable                   | Default          | Description                |
| -------------------------- | ---------------- | -------------------------- |
| `GOOGLE_AI_API_KEY`        | -                | Your Google AI API key     |
| `ROBIN_GEMINI_MODEL`       | `gemini-1.5-pro` | Gemini model to use        |
| `ROBIN_GEMINI_MAX_TOKENS`  | `4000`           | Maximum tokens in response |
| `ROBIN_GEMINI_TEMPERATURE` | `0.3`            | Creativity level (0.0-1.0) |
| `ROBIN_GEMINI_TIMEOUT`     | `60`             | Request timeout in seconds |

### Temperature Settings

- **0.0-0.2**: Very focused, consistent output
- **0.3-0.5**: Balanced creativity and consistency (recommended)
- **0.6-0.8**: More creative, varied output
- **0.9-1.0**: Maximum creativity

## Troubleshooting

### Common Issues

#### 1. API Key Error

```
❌ Gemini is not properly configured.
Please set GOOGLE_AI_API_KEY in your .env file.
```

**Solution:**

- Verify your API key is correct
- Ensure the key starts with `AIza`
- Check that the Generative Language API is enabled

#### 2. Model Not Found

```
❌ Gemini model 'invalid-model' is not available.
Available models: gemini-1.5-pro, gemini-1.5-flash, gemini-1.0-pro, gemini-1.0-pro-vision
```

**Solution:**

- Use one of the supported model names
- Check for typos in the model name

#### 3. Rate Limiting

```
❌ Error generating tests: Gemini API Error: Rate limit exceeded
```

**Solution:**

- Wait a few minutes before retrying
- Check your Google AI usage limits
- Consider upgrading your Google AI plan

#### 4. Timeout Issues

```
❌ Error generating tests: Gemini Error: Request timeout
```

**Solution:**

- Increase `ROBIN_GEMINI_TIMEOUT` in your `.env`
- Try a faster model like `gemini-1.5-flash`
- Check your internet connection

#### 5. Authentication Error

```
❌ Error generating tests: Gemini API Error: Invalid API key
```

**Solution:**

- Regenerate your API key
- Ensure the key is copied correctly
- Check that the API is enabled in Google Cloud Console

### Debug Mode

Enable debug mode to see detailed error information:

```bash
# Set debug mode
ROBIN_DEBUG=true php artisan robin:generate-tests --provider=gemini

# Or add to your .env
ROBIN_DEBUG=true
```

## Performance Tips

### 1. Model Selection

- Use `gemini-1.5-flash` for faster development cycles
- Use `gemini-1.5-pro` for production-quality tests

### 2. Token Management

- Adjust `ROBIN_GEMINI_MAX_TOKENS` based on your needs
- Lower values = faster responses, higher values = more detailed tests

### 3. Temperature Optimization

- Use `0.3` for consistent, reliable test generation
- Use `0.1` for very focused, predictable output
- Use `0.5` for more creative test scenarios

### 4. Batch Processing

- Generate tests for multiple classes at once
- Use `--force` sparingly to avoid unnecessary API calls

## Cost Optimization

### 1. Monitor Usage

- Check your Google AI usage in the [Google Cloud Console](https://console.cloud.google.com/)
- Set up billing alerts

### 2. Efficient Usage

- Use `--dry-run` to preview before generating
- Target specific classes/functions instead of full regeneration
- Use appropriate model sizes for your needs

### 3. Caching

- Robin caches results to avoid regenerating unchanged code
- Use the `.robin` directory to track changes efficiently

## Integration Examples

### With CI/CD Pipeline

```yaml
# .github/workflows/test-generation.yml
name: Generate Tests
on: [push, pull_request]

jobs:
  generate-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: "8.2"
      - name: Install dependencies
        run: composer install
      - name: Generate tests with Gemini
        env:
          GOOGLE_AI_API_KEY: ${{ secrets.GOOGLE_AI_API_KEY }}
          ROBIN_AI_PROVIDER: gemini
        run: php artisan robin:generate-tests --provider=gemini
```

### With Docker

```dockerfile
# Dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set environment variables
ENV ROBIN_AI_PROVIDER=gemini
ENV ROBIN_GEMINI_MODEL=gemini-1.5-pro

# Copy application
COPY . /var/www/html
WORKDIR /var/www/html

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Generate tests
CMD ["php", "artisan", "robin:generate-tests", "--provider=gemini"]
```

## Best Practices

### 1. Security

- Never commit API keys to version control
- Use environment variables for sensitive data
- Rotate API keys regularly

### 2. Testing Strategy

- Generate tests for critical business logic first
- Review generated tests before committing
- Use generated tests as a starting point, not final code

### 3. Maintenance

- Keep Robin updated for latest features
- Monitor API usage and costs
- Regularly review and update test generation prompts

### 4. Team Collaboration

- Document your testing strategy
- Share configuration best practices
- Establish review processes for generated tests

## Support

If you encounter issues with Gemini integration:

1. **Check the troubleshooting section above**
2. **Review Google AI documentation**: [https://ai.google.dev/](https://ai.google.dev/)
3. **Check Robin GitHub issues**: [https://github.com/genericmilk/robin/issues](https://github.com/genericmilk/robin/issues)
4. **Contact Google AI support**: [https://ai.google.dev/support](https://ai.google.dev/support)

---

**Happy testing with Gemini! 🚀**
