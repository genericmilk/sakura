# Setting up Ollama with Robin

This guide will help you set up Ollama to use with Robin for local test generation.

## Prerequisites

- macOS, Linux, or Windows
- At least 8GB RAM (16GB recommended)
- 10GB+ free disk space

## Installation

### macOS/Linux

```bash
curl -fsSL https://ollama.ai/install.sh | sh
```

### Windows

1. Download the installer from [https://ollama.ai/download](https://ollama.ai/download)
2. Run the installer and follow the prompts

## Starting Ollama

```bash
ollama serve
```

This will start the Ollama server on `http://localhost:11434`.

## Recommended Models for Code Generation

### CodeLlama (Recommended)

```bash
ollama pull codellama
```

- **Size**: ~4GB
- **Quality**: Excellent for code generation
- **Speed**: Fast inference

### Llama2 13B

```bash
ollama pull llama2:13b
```

- **Size**: ~7GB
- **Quality**: Very good for code generation
- **Speed**: Moderate inference

### Mistral

```bash
ollama pull mistral
```

- **Size**: ~4GB
- **Quality**: Good for code generation
- **Speed**: Fast inference

### DeepSeek Coder

```bash
ollama pull deepseek-coder
```

- **Size**: ~6GB
- **Quality**: Excellent for code generation
- **Speed**: Moderate inference

## Configuring Robin for Ollama

1. **Set the AI provider in your `.env` file:**

```env
ROBIN_AI_PROVIDER=ollama
```

2. **Configure Ollama settings:**

```env
ROBIN_OLLAMA_BASE_URL=http://localhost:11434
ROBIN_OLLAMA_MODEL=codellama
ROBIN_OLLAMA_MAX_TOKENS=4000
ROBIN_OLLAMA_TEMPERATURE=0.3
ROBIN_OLLAMA_TIMEOUT=120
```

3. **Test the configuration:**

```bash
php artisan robin:generate-tests --provider=ollama --dry-run
```

## Performance Tips

### For Faster Generation

- Use smaller models like `codellama` or `mistral`
- Reduce `ROBIN_OLLAMA_MAX_TOKENS` to 2000-3000
- Ensure you have sufficient RAM available

### For Better Quality

- Use larger models like `llama2:13b` or `deepseek-coder`
- Increase `ROBIN_OLLAMA_MAX_TOKENS` to 6000-8000
- Set `ROBIN_OLLAMA_TEMPERATURE` to 0.1-0.2 for more focused output

### Memory Management

- Close other applications when running large models
- Monitor memory usage with `htop` or Task Manager
- Consider using quantized models for lower memory usage

## Troubleshooting

### Ollama Not Starting

```bash
# Check if Ollama is running
curl http://localhost:11434/api/tags

# Restart Ollama
pkill ollama
ollama serve
```

### Model Not Found

```bash
# List available models
ollama list

# Pull the model again
ollama pull codellama
```

### Slow Generation

- Check your system resources
- Try a smaller model
- Reduce max tokens
- Ensure Ollama has enough RAM allocated

### Connection Timeout

- Increase `ROBIN_OLLAMA_TIMEOUT` in your `.env`
- Check if Ollama is running: `curl http://localhost:11434/api/tags`
- Restart Ollama if needed

## Example Usage

```bash
# Generate tests using Ollama
php artisan robin:generate-tests --provider=ollama

# Generate tests for a specific class
php artisan robin:generate-tests --class=UserController --provider=ollama

# Force regenerate all tests
php artisan robin:generate-tests --force --provider=ollama
```

## Model Comparison

| Model          | Size | Quality    | Speed      | Memory     | Best For         |
| -------------- | ---- | ---------- | ---------- | ---------- | ---------------- |
| codellama      | ~4GB | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Production       |
| llama2:13b     | ~7GB | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐   | ⭐⭐⭐⭐   | High Quality     |
| mistral        | ~4GB | ⭐⭐⭐⭐   | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Fast Development |
| deepseek-coder | ~6GB | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐   | ⭐⭐⭐⭐   | Code Focused     |

## Security and Privacy

Using Ollama with Robin provides several privacy benefits:

- **Local Processing**: All code analysis and test generation happens locally
- **No Data Transmission**: Your code never leaves your machine
- **No API Costs**: Completely free to use
- **Custom Models**: You can fine-tune models for your specific needs

This makes Ollama an excellent choice for:

- Proprietary codebases
- Sensitive applications
- Development teams with privacy requirements
- Cost-conscious organizations
