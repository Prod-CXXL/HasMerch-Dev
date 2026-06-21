# Architecture

## Overview

HasMerch is a static website hosted on Cloudflare Pages and managed through GitHub.

## Current Architecture

User
 ↓
Cloudflare Pages
 ↓
Static Website
 ↓
HTML / CSS / JavaScript

## Source Control

Developer
 ↓
Local Development
 ↓
GitHub Repository
 ↓
Cloudflare Deployment

## Planned Payment Architecture

Customer
 ↓
HasMerch Website
 ↓
Stripe Checkout
 ↓
Order Processing

## Planned Marketplace Architecture

Customer
 ↓
HasMerch
 ↓
Creator Storefront
 ↓
Stripe Connect
 ↓
Creator Payout

## Directory Structure

/
├── website/
├── assets/
├── docs/
├── data/
├── README.md
├── AGENTS.md
├── ROADMAP.md
├── TODO.md
├── ARCHITECTURE.md
└── STYLEGUIDE.md

## Design Principles

- Mobile-first
- Secure by default
- Simple architecture
- Minimal dependencies
- Easy maintenance