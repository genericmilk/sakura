<div align="center">
  <img src="sakura.png" alt="Sakura Logo" width="200" height="200">
  
  # Sakura v1.0.0
  
  [![Tests](https://github.com/genericmilk/sakura/workflows/Tests/badge.svg)](https://github.com/genericmilk/sakura/actions)
  [![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://packagist.org/packages/genericmilk/sakura)
  [![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE.md)
  
  🤖 Sakura is a Laravel package that automatically generates comprehensive tests for your PHP classes and functions using OpenAI's GPT-4o-mini, Anthropic's Claude, Google's Gemini, or local Ollama models.
</div>

## Features

- **Smart Code Analysis**: Automatically detects and tracks changes in your PHP classes and functions
- **Intelligent Test Generation**: Uses OpenAI, Claude, Gemini, or Ollama to generate comprehensive Feature and Unit tests
- **Framework Detection**: Automatically detects whether your project uses Pest or PHPUnit
- **Change Tracking**: Only generates tests for code that has changed or is new
- **Flexible Configuration**: Highly configurable to match your project structure
- **Progress Tracking**: Beautiful CLI interface with progress bars and detailed feedback
- **Multiple AI Providers**: Support for OpenAI API, Claude API, Gemini API, and local Ollama models

## Installation

1. **Install the package via Composer:**

```bash
composer require Genericmilk/sakura
```

2. **Publish the configuration file:**

```bash
php artisan vendor:publish --tag=sakura-config
```

3. **Configure your AI provider:**

### For OpenAI:

```env
sakura_AI_PROVIDER=openai
OPENAI_API_KEY=your-openai-api-key-here
```

### For Claude:

```env
sakura_AI_PROVIDER=claude
ANTHROPIC_API_KEY=your-anthropic-api-key-here
```

### For Gemini:

```env
sakura_AI_PROVIDER=gemini
GOOGLE_AI_API_KEY=your-google-ai-api-key-here
```

### For Ollama:

```env
sakura_AI_PROVIDER=ollama
sakura_OLLAMA_BASE_URL=http://localhost:11434
sakura_OLLAMA_MODEL=codellama
```

4. **Optional: Configure additional settings in your `.env` file:**

```env
# AI Provider (openai, claude, gemini, or ollama)
sakura_AI_PROVIDER=openai

# OpenAI Settings
sakura_OPENAI_MODEL=gpt-4o-mini
sakura_MAX_TOKENS=4000
sakura_TEMPERATURE=0.3

# Claude Settings
sakura_CLAUDE_MODEL=claude-3-5-sonnet-20241022
sakura_CLAUDE_MAX_TOKENS=4000
sakura_CLAUDE_TEMPERATURE=0.3

# Gemini Settings
sakura_GEMINI_MODEL=gemini-1.5-pro
sakura_GEMINI_MAX_TOKENS=4000
sakura_GEMINI_TEMPERATURE=0.3
sakura_GEMINI_TIMEOUT=60

# Ollama Settings
sakura_OLLAMA_BASE_URL=http://localhost:11434
sakura_OLLAMA_MODEL=codellama
sakura_OLLAMA_MAX_TOKENS=4000
sakura_OLLAMA_TEMPERATURE=0.3
sakura_OLLAMA_TIMEOUT=120

# Testing Configuration
sakura_TEST_FRAMEWORK=auto
sakura_TEST_DIRECTORY=tests
sakura_GENERATE_FEATURE_TESTS=true
sakura_GENERATE_UNIT_TESTS=true
sakura_MAX_TESTS_PER_CLASS=10
```

## Usage

### Basic Usage

Generate tests for all changed/new code:

```bash
php artisan sakura:generate-tests
```

### Advanced Usage

**Force regeneration of all tests:**

```bash
php artisan sakura:generate-tests --force
```

**Generate tests for a specific class:**

```bash
php artisan sakura:generate-tests --class=UserController
```

**Generate tests for a specific function:**

```bash
php artisan sakura:generate-tests --function=calculateTotal
```

**Dry run (see what would be generated without creating files):**

```bash
php artisan sakura:generate-tests --dry-run
```

**Override AI provider for this run:**

```bash
php artisan sakura:generate-tests --provider=gemini
```

## AI Providers

### OpenAI

- **Pros**: High-quality test generation, reliable API, fast response times
- **Cons**: Requires API key, costs per request
- **Best for**: Production environments, high-quality test generation
- **Models**: GPT-4o-mini, GPT-4o, GPT-4-turbo

### Claude

- **Pros**: Excellent code understanding, high-quality output, strong reasoning
- **Cons**: Requires API key, costs per request, slightly slower than OpenAI
- **Best for**: Complex codebases, high-quality test generation, reasoning-heavy tasks
- **Models**: Claude 3.5 Sonnet, Claude 3.5 Haiku, Claude 3 Opus

### Gemini

- **Pros**: Fast response times, good code understanding, competitive pricing
- **Cons**: Requires API key, costs per request, newer API
- **Best for**: Fast development cycles, cost-effective test generation
- **Models**: Gemini 1.5 Pro, Gemini 1.5 Flash, Gemini 1.0 Pro

### Ollama

- **Pros**: Free, runs locally, no API costs, privacy-focused
- **Cons**: Requires local setup, model quality varies, slower inference
- **Best for**: Development environments, privacy-conscious teams
- **Models**: CodeLlama, Llama2, Mistral, DeepSeek Coder

#### Setting up Gemini

1. **Get a Google AI API key:**

   - Visit [https://makersuite.google.com/app/apikey](https://makersuite.google.com/app/apikey)
   - Create an account and generate an API key

2. **Configure sakura:**

```env
sakura_AI_PROVIDER=gemini
GOOGLE_AI_API_KEY=your-api-key-here
sakura_GEMINI_MODEL=gemini-1.5-pro
```

3. **Test the configuration:**

```bash
php artisan sakura:generate-tests --provider=gemini --dry-run
```

#### Setting up Claude

1. **Get an Anthropic API key:**

   - Visit [https://console.anthropic.com/](https://console.anthropic.com/)
   - Create an account and generate an API key

2. **Configure sakura:**

```env
sakura_AI_PROVIDER=claude
ANTHROPIC_API_KEY=your-api-key-here
sakura_CLAUDE_MODEL=claude-3-5-sonnet-20241022
```

3. **Test the configuration:**

```bash
php artisan sakura:generate-tests --provider=claude --dry-run
```

#### Setting up Ollama

1. **Install Ollama:**

```bash
# macOS/Linux
curl -fsSL https://ollama.ai/install.sh | sh

# Windows
# Download from https://ollama.ai/download
```

2. **Start Ollama:**

```bash
ollama serve
```

3. **Pull a code model:**

```bash
ollama pull codellama
# or
ollama pull llama2:13b
# or
ollama pull mistral
```

4. **Configure sakura:**

```env
sakura_AI_PROVIDER=ollama
sakura_OLLAMA_MODEL=codellama
```

## How It Works

### 1. Code Analysis

sakura analyzes your codebase and creates a tree of all PHP classes and functions in the configured directories:

- Controllers
- Services
- Actions
- Enums
- Commands
- Models
- Repositories
- Jobs
- Listeners
- Notifications

### 2. Change Detection

sakura tracks changes by:

- Computing hashes of class content and individual methods
- Comparing current code with previously saved state
- Detecting new classes and functions
- Identifying modified methods

### 3. Test Generation

For each changed/new item, sakura:

- Determines the appropriate test type (Feature vs Unit)
- Detects your testing framework (Pest or PHPUnit)
- Generates comprehensive tests using your chosen AI provider
- Saves tests to the appropriate directory structure

### 4. Test Organization

Tests are organized following Laravel conventions:

- **Feature tests**: `tests/Feature/` for controllers and HTTP-related code
- **Unit tests**: `tests/Unit/` for services, actions, and business logic
- **Directory structure**: Mirrors your app directory structure

## Configuration

### AI Provider Settings

```php
'provider' => env('sakura_AI_PROVIDER', 'openai'), // openai, claude, gemini, ollama
```

### OpenAI Settings

```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('sakura_OPENAI_MODEL', 'gpt-4o-mini'),
    'max_tokens' => env('sakura_MAX_TOKENS', 4000),
    'temperature' => env('sakura_TEMPERATURE', 0.3),
],
```

### Claude Settings

```php
'claude' => [
    'api_key' => env('ANTHROPIC_API_KEY'),
    'model' => env('sakura_CLAUDE_MODEL', 'claude-3-5-sonnet-20241022'),
    'max_tokens' => env('sakura_CLAUDE_MAX_TOKENS', 4000),
    'temperature' => env('sakura_CLAUDE_TEMPERATURE', 0.3),
],
```

### Gemini Settings

```php
'gemini' => [
    'api_key' => env('GOOGLE_AI_API_KEY'),
    'model' => env('sakura_GEMINI_MODEL', 'gemini-1.5-pro'),
    'max_tokens' => env('sakura_GEMINI_MAX_TOKENS', 4000),
    'temperature' => env('sakura_GEMINI_TEMPERATURE', 0.3),
    'timeout' => env('sakura_GEMINI_TIMEOUT', 60),
],
```

### Ollama Settings

```php
'ollama' => [
    'base_url' => env('sakura_OLLAMA_BASE_URL', 'http://localhost:11434'),
    'model' => env('sakura_OLLAMA_MODEL', 'codellama'),
    'max_tokens' => env('sakura_OLLAMA_MAX_TOKENS', 4000),
    'temperature' => env('sakura_OLLAMA_TEMPERATURE', 0.3),
    'timeout' => env('sakura_OLLAMA_TIMEOUT', 120),
],
```

### Analysis Directories

```php
'analysis' => [
    'directories' => [
        'app/Http/Controllers',
        'app/Services',
        'app/Actions',
        'app/Enums',
        'app/Console/Commands',
        'app/Models',
        'app/Repositories',
        'app/Jobs',
        'app/Listeners',
        'app/Notifications',
    ],
    'file_extensions' => ['php'],
    'exclude_patterns' => [
        '*Test.php',
        '*Tests.php',
        'TestCase.php',
    ],
],
```

### Testing Configuration

```php
'testing' => [
    'framework' => env('sakura_TEST_FRAMEWORK', 'auto'), // auto, pest, phpunit
    'test_directory' => env('sakura_TEST_DIRECTORY', 'tests'),
    'generate_feature_tests' => env('sakura_GENERATE_FEATURE_TESTS', true),
    'generate_unit_tests' => env('sakura_GENERATE_UNIT_TESTS', true),
    'max_tests_per_class' => env('sakura_MAX_TESTS_PER_CLASS', 10),
],
```

## File Structure

After installation, sakura creates a `.sakura` directory in your project root:

```
your-project/
├── .sakura/
│   ├── code-tree.json    # Tracks your code structure and hashes
│   └── cache.json        # Cached data for performance
├── tests/
│   ├── Feature/          # Generated feature tests
│   └── Unit/             # Generated unit tests
└── config/
    └── sakura.php         # Configuration file
```

## Example Output

When you run `php artisan sakura:generate-tests`, you'll see output like:

```
🤖 sakura - AI-Powered Test Generator

🤖 Using Gemini for test generation

📊 Analyzing codebase...
Found 3 items to process:
📝 Changed Classes:
  - App\Http\Controllers\UserController (app/Http/Controllers/UserController.php)
🆕 New Classes:
  - App\Services\EmailService (app/Services/EmailService.php)
  - App\Actions\CreateUser (app/Actions/CreateUser.php)

Do you want to generate tests for these items? (yes/no) [no]:
> yes

[████████████████████████████████████████] 100%

✅ Generated tests for UserController → tests/Feature/Http/Controllers/UserControllerTest.php
✅ Generated tests for EmailService → tests/Unit/Services/EmailServiceTest.php
✅ Generated tests for CreateUser → tests/Unit/Actions/CreateUserTest.php

📊 Summary: 3 successful, 0 failed
💾 Updating code tree...
✅ Test generation completed!
```

## Generated Test Examples

### Pest Feature Test (Controller)

```php
<?php

use App\Models\User;

test('user can view their profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/profile');

    $response->assertStatus(200)
        ->assertViewIs('profile')
        ->assertViewHas('user', $user);
});

test('guest cannot access profile page', function () {
    $response = $this->get('/profile');

    $response->assertRedirect('/login');
});
```

### PHPUnit Unit Test (Service)

```php
<?php

namespace Tests\Unit\Services;

use App\Services\EmailService;
use Tests\TestCase;
use Mockery;

class EmailServiceTest extends TestCase
{
    public function test_send_welcome_email()
    {
        $emailService = new EmailService();
        $user = User::factory()->create();

        $result = $emailService->sendWelcomeEmail($user);

        $this->assertTrue($result);
        // Additional assertions...
    }
}
```

## Provider Comparison

| Feature           | OpenAI      | Claude      | Gemini      | Ollama     |
| ----------------- | ----------- | ----------- | ----------- | ---------- |
| **Cost**          | Per request | Per request | Per request | Free       |
| **Speed**         | ⭐⭐⭐⭐⭐  | ⭐⭐⭐⭐    | ⭐⭐⭐⭐⭐  | ⭐⭐⭐     |
| **Quality**       | ⭐⭐⭐⭐⭐  | ⭐⭐⭐⭐⭐  | ⭐⭐⭐⭐    | ⭐⭐⭐⭐   |
| **Privacy**       | ⭐⭐        | ⭐⭐        | ⭐⭐        | ⭐⭐⭐⭐⭐ |
| **Setup**         | ⭐⭐⭐⭐⭐  | ⭐⭐⭐⭐⭐  | ⭐⭐⭐⭐⭐  | ⭐⭐⭐     |
| **Customization** | ⭐⭐⭐      | ⭐⭐⭐      | ⭐⭐⭐      | ⭐⭐⭐⭐⭐ |

## Troubleshooting

### OpenAI Issues

- **API Key Error**: Ensure `OPENAI_API_KEY` is set in your `.env` file
- **Rate Limiting**: Check your OpenAI usage limits
- **Model Not Found**: Verify the model name in your configuration

### Claude Issues

- **API Key Error**: Ensure `ANTHROPIC_API_KEY` is set in your `.env` file
- **Rate Limiting**: Check your Anthropic usage limits
- **Model Not Found**: Verify the model name in your configuration
- **Timeout**: Claude responses can be slower, increase timeout if needed

### Gemini Issues

- **API Key Error**: Ensure `GOOGLE_AI_API_KEY` is set in your `.env` file
- **Rate Limiting**: Check your Google AI usage limits
- **Model Not Found**: Verify the model name in your configuration
- **Timeout**: Increase `sakura_GEMINI_TIMEOUT` if needed

### Ollama Issues

- **Connection Error**: Ensure Ollama is running with `ollama serve`
- **Model Not Found**: Pull the model with `ollama pull <model-name>`
- **Timeout**: Increase `sakura_OLLAMA_TIMEOUT` for slower models

### General Issues

- **No Changes Detected**: Use `--force` to regenerate all tests
- **Test Quality**: Adjust temperature settings for more/less creative tests
- **Framework Detection**: Manually set `sakura_TEST_FRAMEWORK` if auto-detection fails

## Requirements

- PHP 8.1+
- Laravel 10.0+ or 11.0+
- OpenAI API key (for OpenAI provider)
- Anthropic API key (for Claude provider)
- Google AI API key (for Gemini provider)
- Ollama (for Ollama provider)
- Composer

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

If you encounter any issues or have questions, please open an issue on GitHub.

---

**Made with ❤️ by Genericmilk**
