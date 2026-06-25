# Wrapped by Vee

A lightweight analytics wrapper for your projects that provides clean, simple tracking without the complexity.

## Features

- Simple integration with minimal setup
- Lightweight footprint on your application
- Privacy-focused data collection
- Easy-to-read analytics dashboard
- Custom event tracking
- User session management
- Performance metrics

## Quick Start

```bash
npm install wrapped-by-vee
```

```javascript
import { Wrapped } from 'wrapped-by-vee';

const analytics = new Wrapped({
  projectId: 'your-project-id',
  apiKey: 'your-api-key'
});

analytics.track('page_view', {
  page: '/home',
  referrer: document.referrer
});
```

## Analytics Dashboard

The dashboard provides:
- Real-time event tracking
- User engagement metrics
- Geographic distribution
- Device and browser statistics
- Custom event analysis

## Configuration

```javascript
const config = {
  projectId: 'your-project-id',
  apiKey: 'your-api-key',
  options: {
    debug: false,
    sampleRate: 1.0,
    trackIP: false,
    trackUserAgent: true
  }
};
```

## Available Methods

- `track(event, properties)` - Track custom events
- `identify(userId, traits)` - Identify users
- `page(name, properties)` - Track page views
- `setConsent(consent)` - Set consent preferences
- `destroy()` - Clean up analytics instance



---

Moving to GitHub now. We'll finish the implementation tomorrow. The core structure is ready for the final touches.
