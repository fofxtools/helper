# Project Structure

The Helper library is a lightweight collection of utility functions and classes designed to improve developer productivity and code maintainability.

```
helper/
├── config/
│   ├── helper.config.php         # Optional configuration file (example version provided)
│   └── helper.constants.php      # Global constant definitions
├── public/
│   ├── index.php                 # Demonstration script for Tracker
│   └── request_vars.php          # Utility script for printing request variables
├── src/
│   ├── array.php                 # Array-related utility functions
│   ├── buffer.php                # Output buffering helpers
│   ├── Capture.php               # Output capturing class
│   ├── datetime.php              # Date/time utilities
│   ├── file.php                  # File and logging utilities
│   ├── functions.php             # Core utility logic (e.g., config resolution)
│   ├── math.php                  # Math utilities
│   ├── memory.php                # Memory utilities
│   ├── Numbers.php               # Number to words conversion
│   ├── ReflectionUtils.php       # Reflection-based argument parsing
│   ├── scraping.php              # Web scraping helpers
│   ├── server.php                # Server/environment utilities
│   ├── string.php                # String utilities
│   ├── text.php                  # Text formatting helpers
│   └── Tracker.php               # Script performance tracking
├── tests/                        # Unit tests
├── composer.json                 # Package definition
├── phpunit.xml                   # PHPUnit configuration
├── phpstan.neon                  # PHPStan configuration
└── .php-cs-fixer.php             # Code style configuration
```

## Core Components

### Tracker Class
Located in `src/Tracker.php`

The Tracker class is the main component for performance monitoring:
- Tracks script execution time
- Monitors memory usage
- Measures bandwidth
- Uses a Singleton pattern for global access

### ReflectionUtils Class
Located in `src/ReflectionUtils.php`

Provides utilities for working with PHP's reflection system:
- Extracts bound arguments from methods
- Works with PHP's backtrace system
- Handles both functions and methods
- Supports argument exclusion

### Capture Class
Located in `src/Capture.php`

Manages captured output for debugging or display. Allows for:

- Capturing output from function calls
- Capturing output from evaluated PHP code
- Storing multiple captured outputs
- Printing captured outputs in different formats

This class is particularly useful for debugging, testing, and scenarios where output needs to be captured and processed rather than directly displayed.

The class allows captured outputs to be retrieved, printed, or formatted with HTML tags for display.

### Utility Functions
Located in various files under `src/`

Core utility functions organized by domain:
- Array operations (`array.php`)
- Output buffering (`buffer.php`)
- Date/time handling (`datetime.php`)
- File operations (`file.php`)
- Math operations (`math.php`)
- Memory utilities (`memory.php`)
- Number to words conversion (`Numbers.php`)
- Web scraping (`scraping.php`)
- Server utilities (`server.php`)
- String utilities (`string.php`)
- Text formatting (`text.php`)

## Design Patterns

- **Singleton**: Tracker uses a singleton pattern for centralized timing
- **Functional Utilities**: Most features are implemented as reusable standalone functions
- **Autoloading**: PSR-4 compliant autoloading via Composer

## Configuration

The `config/helper.config.php` file controls global settings:
- Auto-start behavior
- Testing environment detection
- Other configuration options

## Development Tools

- **PHPUnit**: Unit testing framework
- **PHPStan**: Static analysis tool
- **PHP CS Fixer**: Code style enforcement