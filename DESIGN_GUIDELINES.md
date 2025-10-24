# Café Gervacio's Design Guidelines

## Overview
This document outlines the design system for the Seat Management System, ensuring consistent and accessible user interfaces across all components.

---

## Table of Contents
1. [Brand Identity](#brand-identity)
2. [Typography](#typography)
3. [Color System](#color-system)
4. [Spacing & Layout](#spacing--layout)
5. [Components](#components)
6. [Accessibility](#accessibility)
7. [Contact Information](#contact-information)

---

## Brand Identity

### Mission
To provide a seamless, inclusive, and efficient seating experience for all customers at Café Gervacio's.

### Vision
A modern café experience where technology enhances hospitality without compromising warmth and personal service.

### Core Values
- **Inclusivity**: Priority support for seniors, PWD, and pregnant guests
- **Efficiency**: Quick registration and minimal wait times
- **Transparency**: Clear communication about queue status
- **Accessibility**: Easy-to-use interfaces for all age groups

---

## Typography

### Font Family
- **Primary**: Inter (all weights)
- **Fallback**: System UI fonts

### Font Sizes (Responsive)
```css
--text-xs:   0.75rem - 0.875rem
--text-sm:   0.875rem - 1rem
--text-base: 1rem - 1.125rem
--text-lg:   1.125rem - 1.25rem
--text-xl:   1.25rem - 1.5rem
--text-2xl:  1.5rem - 1.875rem
--text-3xl:  1.875rem - 2.25rem
--text-4xl:  2.25rem - 3rem
```

### Font Weights
- **Normal**: 400
- **Medium**: 500
- **Semibold**: 600
- **Bold**: 700
- **Extrabold**: 800

### Typography Best Practices
1. ✅ **Use Inter font consistently** across all interfaces
2. ✅ **Use fluid typography** (clamp) for better responsiveness
3. ✅ **Line height**: 1.5 for body text, 1.25 for headings
4. ✅ **Contrast**: Minimum 4.5:1 for normal text, 3:1 for large text
5. ❌ **Avoid**: Mixing multiple font families
6. ❌ **Avoid**: Font sizes below 14px (except for fine print)

---

## Color System

### Brand Colors
```css
Primary (Dark Blue):
- Default: #09121E
- Light:   #1a2332
- Dark:    #000814
```

### Accent Colors
```css
Cream:  #EEEDE7  (Used for primary CTAs on dark backgrounds)
Yellow: #FDB813  (Highlight/emphasis)
Orange: #F59E0B  (Secondary emphasis)
```

### Status Colors
```css
Success: #10B981  (Green - positive actions, confirmations)
Warning: #F59E0B  (Orange - important notices, requires attention)
Danger:  #EF4444  (Red - errors, critical issues ONLY)
Info:    #3B82F6  (Blue - informational messages)
```

### Usage Guidelines

#### ✅ DO:
- Use **Warning (Orange)** for form validation errors
- Use **Info (Blue)** for helpful tips and guidance
- Use **Success (Green)** for completed actions
- Use **Danger (Red)** ONLY for critical system errors
- Maintain consistent color usage across all screens

#### ❌ DON'T:
- Don't use red text for minor issues or form validation
- Don't use too many colors in a single view
- Don't use color as the only indicator (use icons too)

### Color Contrast Requirements
- **Normal text**: 4.5:1 minimum contrast ratio
- **Large text** (18px+): 3:1 minimum contrast ratio
- **Interactive elements**: Clear visual distinction from static content

---

## Spacing & Layout

### Spacing Scale
```css
--space-xs:  0.25rem  (4px)
--space-sm:  0.5rem   (8px)
--space-md:  1rem     (16px)
--space-lg:  1.5rem   (24px)
--space-xl:  2rem     (32px)
--space-2xl: 3rem     (48px)
--space-3xl: 4rem     (64px)
--space-4xl: 6rem     (96px)
```

### Layout Principles
1. **White Space**: Generous spacing improves readability
2. **Consistency**: Use the spacing scale uniformly
3. **Hierarchy**: Larger spacing for section breaks, smaller for related items
4. **Responsive**: Adjust spacing for mobile (reduce by 25-50%)

### Grid System
- **Desktop**: Max-width 1280px, 12-column grid
- **Tablet**: Max-width 768px, flexible columns
- **Mobile**: Max-width 640px, single column

---

## Components

### Buttons

#### Primary Button
**When to use**: Main action, most important action on the page
```html
<button class="btn btn-primary btn-lg">
    Continue
</button>
```
- Background: Primary dark (#09121E)
- Text: White
- Hover: Lifts with shadow
- States: Default, Hover, Active, Disabled

#### Secondary Button
**When to use**: Secondary actions, cancel actions
```html
<button class="btn btn-secondary btn-lg">
    Go Back
</button>
```
- Background: White
- Border: Gray-300
- Text: Gray-700
- Hover: Gray-50 background

#### Accent Button
**When to use**: CTAs on dark backgrounds
```html
<button class="btn btn-accent btn-xl">
    <i class="fas fa-chair"></i>
    Tap to Get a Seat
</button>
```
- Background: Cream (#EEEDE7)
- Text: Primary dark

### Form Elements

#### Input Fields
```html
<input type="text" class="input-field" placeholder="Enter your name">
```

#### Input with Label
```html
<label class="input-label">Name/Nickname *</label>
<input type="text" class="input-field">
<span class="input-helper">Enter your name or nickname</span>
```

#### Error State
```html
<input type="text" class="input-field input-error">
<div class="input-error-message">
    <i class="fas fa-exclamation-circle"></i>
    <span>Please enter a valid name</span>
</div>
```

### Cards
```html
<div class="card card-elevated">
    <div class="card-header">
        <h3>Card Title</h3>
    </div>
    <div class="card-body">
        <p>Card content goes here</p>
    </div>
    <div class="card-footer">
        <button class="btn btn-primary">Action</button>
    </div>
</div>
```

---

## Accessibility

### WCAG 2.1 AA Compliance

#### Visual Accessibility
- ✅ Color contrast ratios meet minimum standards
- ✅ Focus indicators visible on all interactive elements
- ✅ Text resizable up to 200% without loss of functionality
- ✅ No information conveyed by color alone

#### Keyboard Navigation
- ✅ All interactive elements accessible via keyboard
- ✅ Logical tab order throughout the interface
- ✅ Escape key closes modals/overlays
- ✅ Enter/Space activates buttons

#### Screen Readers
- ✅ Semantic HTML elements used correctly
- ✅ ARIA labels for icon-only buttons
- ✅ Alt text for all informative images
- ✅ Form fields have associated labels

#### Motor Impairments
- ✅ Large touch targets (minimum 44x44px)
- ✅ Adequate spacing between clickable elements
- ✅ No time-limited interactions (or generous timeouts)

### Priority Support Features
- **Seniors**: Large text, high contrast, simple navigation
- **PWD**: Wheelchair accessibility info, clear labels
- **Pregnant**: Comfortable seating info, priority queue

---

## Contact Information

### Display Requirements
All kiosk screens should include footer contact information:

```html
<div class="text-center text-gray-600">
    <p>Need assistance? Contact our staff or call</p>
    <p class="font-bold text-gray-900 text-lg mt-1">(082) 123-4567</p>
</div>
```

### Placement
- **Kiosk screens**: Bottom center of screen
- **Admin dashboard**: Header or sidebar
- **Error screens**: Prominently displayed

### Contact Channels
1. **In-person**: Approach any staff member
2. **Phone**: (082) 123-4567 (prominently displayed)
3. **QA Support**: For technical issues

---

## Component Usage Examples

### Registration Form
```html
<form class="space-y-xl">
    <div>
        <h3 class="text-2xl font-bold mb-md">Name/Nickname *</h3>
        <input type="text" class="input-field" placeholder="Enter your name">
        <p class="input-helper">Enter your name or representative's name</p>
    </div>

    <div>
        <h3 class="text-2xl font-bold mb-md">Party Size *</h3>
        <div class="flex items-center space-x-md">
            <button type="button" class="btn btn-secondary">-</button>
            <input type="number" class="input-field text-center" value="1">
            <button type="button" class="btn btn-secondary">+</button>
        </div>
    </div>

    <div class="flex justify-between">
        <button type="button" class="btn btn-secondary btn-lg">Go Back</button>
        <button type="submit" class="btn btn-primary btn-lg">Continue</button>
    </div>
</form>
```

### Status Messages

#### Success
```html
<div class="bg-green-50 border-l-4 border-green-500 p-md">
    <div class="flex items-start">
        <i class="fas fa-check-circle text-green-500 text-xl mr-sm"></i>
        <div>
            <p class="font-bold text-green-800">Registration Successful!</p>
            <p class="text-green-700">Your queue number is #042</p>
        </div>
    </div>
</div>
```

#### Warning/Validation Error
```html
<div class="bg-yellow-50 border-l-4 border-yellow-500 p-md">
    <div class="flex items-start">
        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mr-sm"></i>
        <div>
            <p class="font-bold text-yellow-800">Incomplete Information</p>
            <p class="text-yellow-700">Please enter your name to continue</p>
        </div>
    </div>
</div>
```

#### Error (System Issues Only)
```html
<div class="bg-red-50 border-l-4 border-red-500 p-md">
    <div class="flex items-start">
        <i class="fas fa-times-circle text-red-500 text-xl mr-sm"></i>
        <div>
            <p class="font-bold text-red-800">System Error</p>
            <p class="text-red-700">Unable to connect to server. Please contact staff.</p>
        </div>
    </div>
</div>
```

---

## Responsive Design

### Breakpoints
```css
Mobile:  < 640px
Tablet:  640px - 1024px
Desktop: > 1024px
Kiosk:   900px x 600px (landscape orientation)
```

### Mobile-First Approach
1. Design for mobile first
2. Enhance for larger screens
3. Test on actual devices
4. Ensure touch targets are 44x44px minimum

---

## Animation & Interaction

### Transition Timing
```css
Fast:   150ms  (Hover effects)
Normal: 200ms  (Color changes, small movements)
Slow:   300ms  (Page transitions, modals)
```

### Easing Functions
- **Ease-in-out**: Default for most transitions
- **Ease-out**: Entering elements
- **Ease-in**: Exiting elements

### Hover States
- **Buttons**: Lift with shadow + background color change
- **Cards**: Subtle lift with shadow
- **Links**: Underline + color change

---

## Implementation Checklist

### For New Features
- [ ] Follows typography guidelines
- [ ] Uses approved color palette
- [ ] Maintains consistent spacing
- [ ] Includes all button states (hover, active, disabled)
- [ ] Accessible via keyboard
- [ ] Tested with screen reader
- [ ] Responsive on mobile/tablet/desktop
- [ ] Has error states defined
- [ ] Includes loading states
- [ ] Contact information visible
- [ ] Passes WCAG 2.1 AA standards

### For Updates
- [ ] Maintains visual consistency with existing components
- [ ] Doesn't break existing layouts
- [ ] Tested across all screen sizes
- [ ] Accessibility not degraded

---

## Resources

### Design Tools
- **Figma**: [Link to design files]
- **Color Contrast Checker**: https://webaim.org/resources/contrastchecker/
- **WAVE Accessibility Checker**: https://wave.webaim.org/

### Documentation
- **Tailwind CSS**: https://tailwindcss.com/docs
- **WCAG Guidelines**: https://www.w3.org/WAI/WCAG21/quickref/
- **Font Awesome Icons**: https://fontawesome.com/icons

### Internal
- **Design System CSS**: `/resources/css/design-system.css`
- **Tailwind Config**: `/tailwind.config.js`
- **Component Library**: `/resources/views/components/`

---

## Version History

### v1.0.0 (2025-10-24)
- Initial design system documentation
- Improved color scheme (replaced red with warning/info colors)
- Enhanced typography system
- Added comprehensive button styling
- Improved accessibility guidelines
- Added contact information requirements

---

## Questions or Feedback?

For questions about these guidelines or to suggest improvements:
- Contact the development team
- Call QA Support: (082) 123-4567
- Submit an issue in the project repository

---

**Last Updated**: October 24, 2025
**Maintained by**: Development Team
