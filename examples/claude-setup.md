# Setting up Claude with sakura

This guide will help you set up Anthropic's Claude to use with sakura for high-quality test generation.

## Prerequisites

- An Anthropic account
- API access to Claude models
- Sufficient API credits for your usage

## Getting Started with Claude

### 1. Create an Anthropic Account

1. Visit [https://console.anthropic.com/](https://console.anthropic.com/)
2. Sign up for an account
3. Verify your email address

### 2. Generate an API Key

1. Navigate to the API Keys section in your Anthropic console
2. Click "Create Key"
3. Give your key a descriptive name (e.g., "sakura Test Generation")
4. Copy the generated API key (you won't be able to see it again)

### 3. Configure sakura for Claude

Add the following to your `.env` file:

```env
sakura_AI_PROVIDER=claude
ANTHROPIC_API_KEY=your-api-key-here
sakura_CLAUDE_MODEL=claude-3-5-sonnet-20241022
sakura_CLAUDE_MAX_TOKENS=4000
sakura_CLAUDE_TEMPERATURE=0.3
```

### 4. Test the Configuration

```bash
php artisan sakura:generate-tests --provider=claude --dry-run
```

## Available Claude Models

### Claude 3.5 Sonnet (Recommended)

- **Model ID**: `claude-3-5-sonnet-20241022`
- **Best for**: High-quality test generation, complex codebases
- **Speed**: Fast
- **Cost**: Moderate

### Claude 3.5 Haiku

- **Model ID**: `claude-3-5-haiku-20241022`
- **Best for**: Quick test generation, simple codebases
- **Speed**: Very fast
- **Cost**: Low

### Claude 3 Opus

- **Model ID**: `claude-3-opus-20240229`
- **Best for**: Complex reasoning, high-quality output
- **Speed**: Moderate
- **Cost**: High

### Claude 3 Sonnet

- **Model ID**: `claude-3-sonnet-20240229`
- **Best for**: Balanced quality and speed
- **Speed**: Fast
- **Cost**: Moderate

### Claude 3 Haiku

- **Model ID**: `claude-3-haiku-20240307`
- **Best for**: Quick responses, simple tasks
- **Speed**: Very fast
- **Cost**: Low

## Model Selection Guide

### For Production Use

```env
sakura_CLAUDE_MODEL=claude-3-5-sonnet-20241022
```

- Best overall quality
- Good balance of speed and cost
- Excellent code understanding

### For Development/Testing

```env
sakura_CLAUDE_MODEL=claude-3-5-haiku-20241022
```

- Fastest response times
- Lower cost
- Good for iterative development

### For Complex Codebases

```env
sakura_CLAUDE_MODEL=claude-3-opus-20240229
```

- Highest quality output
- Best reasoning capabilities
- Higher cost

## Configuration Options

### Temperature

Controls creativity vs consistency:

```env
# More creative, varied tests
sakura_CLAUDE_TEMPERATURE=0.7

# More consistent, predictable tests
sakura_CLAUDE_TEMPERATURE=0.1

# Balanced (recommended)
sakura_CLAUDE_TEMPERATURE=0.3
```

### Max Tokens

Controls response length:

```env
# Shorter, focused tests
sakura_CLAUDE_MAX_TOKENS=2000

# Comprehensive tests
sakura_CLAUDE_MAX_TOKENS=6000

# Balanced (recommended)
sakura_CLAUDE_MAX_TOKENS=4000
```

## Usage Examples

### Basic Usage

```bash
# Use Claude as default provider
sakura_AI_PROVIDER=claude php artisan sakura:generate-tests

# Override provider for this run
php artisan sakura:generate-tests --provider=claude
```

### Specific Class Testing

```bash
php artisan sakura:generate-tests --class=UserController --provider=claude
```

### Force Regeneration

```bash
php artisan sakura:generate-tests --force --provider=claude
```

### Dry Run

```bash
php artisan sakura:generate-tests --dry-run --provider=claude
```

## Cost Optimization

### Monitor Usage

- Check your Anthropic console for usage statistics
- Set up billing alerts if needed
- Monitor token usage per request

### Optimize Settings

```env
# For cost-conscious usage
sakura_CLAUDE_MODEL=claude-3-5-haiku-20241022
sakura_CLAUDE_MAX_TOKENS=2000
sakura_CLAUDE_TEMPERATURE=0.1
```

### Batch Processing

- Use `--force` sparingly
- Run tests generation during off-peak hours
- Consider using Ollama for development, Claude for production

## Troubleshooting

### API Key Issues

```bash
# Error: Claude is not properly configured
# Solution: Check your ANTHROPIC_API_KEY in .env
```

### Rate Limiting

```bash
# Error: Claude API Error: Rate limit exceeded
# Solution: Wait and retry, or upgrade your plan
```

### Model Not Found

```bash
# Error: Claude model 'invalid-model' is not available
# Solution: Use one of the supported model IDs
```

### Timeout Issues

```bash
# Error: Request timeout
# Solution: Increase timeout or use a faster model
```

## Best Practices

### 1. Model Selection

- Use Claude 3.5 Sonnet for production
- Use Claude 3.5 Haiku for development
- Use Claude 3 Opus for complex codebases

### 2. Configuration

- Start with default settings
- Adjust temperature based on your needs
- Monitor token usage and costs

### 3. Integration

- Use Claude for high-quality test generation
- Use Ollama for local development
- Use OpenAI as a fallback option

### 4. Testing Strategy

- Generate tests for new features with Claude
- Use dry runs to preview changes
- Review generated tests before committing

## Comparison with Other Providers

| Aspect                 | Claude     | OpenAI     | Ollama     |
| ---------------------- | ---------- | ---------- | ---------- |
| **Code Understanding** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐   |
| **Test Quality**       | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐   |
| **Reasoning**          | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐   | ⭐⭐⭐     |
| **Speed**              | ⭐⭐⭐⭐   | ⭐⭐⭐⭐⭐ | ⭐⭐⭐     |
| **Cost**               | ⭐⭐⭐     | ⭐⭐⭐     | ⭐⭐⭐⭐⭐ |
| **Privacy**            | ⭐⭐       | ⭐⭐       | ⭐⭐⭐⭐⭐ |

## Security Considerations

- **API Key Security**: Store your API key securely in `.env`
- **Code Privacy**: Your code is sent to Anthropic's servers
- **Rate Limiting**: Be aware of API rate limits
- **Cost Monitoring**: Monitor your usage to avoid unexpected charges

## Support

If you encounter issues with Claude integration:

1. Check the [Anthropic API documentation](https://docs.anthropic.com/)
2. Verify your API key and model configuration
3. Check your usage limits in the Anthropic console
4. Open an issue on the sakura GitHub repository

---

**Claude is particularly well-suited for test generation due to its strong reasoning capabilities and excellent understanding of code structure and patterns.**
