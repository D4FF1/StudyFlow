# StudyFlow

Turn Study Chaos Into Progress.

## Overview
StudyFlow is a premium, frontend-only student productivity app that brings tasks, subjects, planning, focus sessions, goals, analytics, achievements, and notifications into one polished dashboard. The app runs entirely in the browser using local storage, so there is no backend, database, or server dependency.

## Problem
Students often manage deadlines, class work, goals, and study time across disconnected tools. That fragmentation creates missed deadlines, weak planning, and low visibility into how much real progress is being made.

## Solution
StudyFlow consolidates study lifecycle management into one SaaS-like experience. It helps students:
- track tasks and priorities
- organize subjects
- plan a weekly study schedule
- focus on the next best task with a recommendation engine
- monitor study streaks and achievements
- review real analytics from local data

## Features
- Dashboard overview with dynamic metrics
- Task creation, editing, filtering, sorting, and completion tracking
- Subject management with progress and study summaries
- Weekly planner with scheduled sessions
- Distraction-free focus mode timer and completion flow
- Goal tracking with milestones and progress
- Premium analytics dashboard with chart visualizations
- Achievement and streak system based on real activity
- Notification center and toast feedback
- Search across tasks, subjects, and goals
- Theme switching, settings, import/export, and reset flows
- Demo data loader for instant app preview

## Priority Engine
StudyFlow includes a client-side priority engine that ranks tasks based on factors such as:
- deadline proximity
- importance
- difficulty
- estimated duration
- progress level
- overdue pressure

The engine produces an adaptive recommendation for the “next best task” and explains the reason behind it in plain language.

## Tech Stack
- HTML5
- Tailwind CSS via CDN
- Vanilla JavaScript ES6+
- Chart.js
- Lucide Icons
- LocalStorage persistence

## How It Works
1. Open the app in a browser.
2. Complete onboarding or skip to the dashboard.
3. Create tasks, subjects, planner sessions, and goals.
4. StudyFlow stores all content locally in browser storage.
5. The app updates progress, analytics, streaks, and achievements automatically.

## Local Development
### Option 1: Open directly
- Clone or download the project.
- Open index.html in your browser.

### Option 2: Use a local static server
Run one of the following commands from the project folder:

- python -m http.server 8000
- py -m http.server 8000

Then open:

- http://localhost:8000

## Data Storage
StudyFlow stores data locally in browser LocalStorage using keys such as:
- studyflow_user
- studyflow_tasks
- studyflow_subjects
- studyflow_sessions
- studyflow_goals
- studyflow_achievements
- studyflow_settings

This ensures the app works completely offline after initial load.

## Screenshots
Add screenshots here after deployment or export from the browser.

## Future Improvements
- More advanced calendar interactions
- Drag-and-drop planner editing
- AI-assisted study recommendations
- Progress sync across devices via optional cloud layer
- More detailed project dashboards and subject filters

## AI Usage Disclosure
AI tools were used as development assistance for brainstorming, debugging, code suggestions, UI iteration, and documentation support. The developer reviewed and adapted the implementation to ensure it meets the project requirement and remains functional in a browser-only environment.

## Hackathon Information
This project was built as a solo frontend hackathon effort focused on creating a polished, functional product experience without a backend. The goal was to design and implement a realistic productivity app that feels like a real student SaaS product while remaining fully client-side and deployable anywhere.

## Deployment
StudyFlow is a static frontend project and can be deployed to:
- GitHub Pages
- Netlify
- Vercel

Typical steps:
1. Push the project folder to a GitHub repository.
2. Connect the repository to GitHub Pages or Netlify.
3. Publish using the default static site options.

No backend or database configuration is required.
