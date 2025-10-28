#!/usr/bin/env node

/**
 * RTM Stream URL Extractor
 * 
 * This Node.js script attempts to extract the direct video stream URL
 * from RTM Klik website using Puppeteer.
 * 
 * Installation:
 *   npm install puppeteer
 * 
 * Usage:
 *   node extract-stream-url.js
 */

const puppeteer = require('puppeteer');

async function extractStreamUrl() {
    console.log('🔍 Extracting RTM TV2 stream URL...\n');
    
    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const page = await browser.newPage();
        
        // Array to store intercepted URLs
        const streamUrls = [];
        
        // Intercept network requests
        await page.setRequestInterception(true);
        
        page.on('request', (request) => {
            const url = request.url();
            
            // Look for video stream URLs
            if (url.includes('.m3u8') || 
                url.includes('.mpd') || 
                url.includes('/playlist') ||
                url.includes('stream')) {
                
                console.log('📹 Found potential stream:', url);
                streamUrls.push(url);
            }
            
            request.continue();
        });
        
        // Listen for responses
        page.on('response', async (response) => {
            const url = response.url();
            const contentType = response.headers()['content-type'] || '';
            
            if (contentType.includes('application/vnd.apple.mpegurl') ||
                contentType.includes('application/dash+xml') ||
                url.includes('.m3u8')) {
                
                console.log('✅ Confirmed stream URL:', url);
                console.log('   Content-Type:', contentType);
                streamUrls.push(url);
            }
        });
        
        console.log('🌐 Loading RTM TV2 page...');
        await page.goto('https://rtmklik.rtm.gov.my/live/tv2', {
            waitUntil: 'networkidle2',
            timeout: 30000
        });
        
        // Wait for video player to load
        console.log('⏳ Waiting for video player...');
        await page.waitForTimeout(5000);
        
        // Try to find video element and its source
        const videoInfo = await page.evaluate(() => {
            const videos = document.querySelectorAll('video');
            const iframes = document.querySelectorAll('iframe');
            
            const info = {
                videoSources: [],
                iframeSources: []
            };
            
            videos.forEach(video => {
                if (video.src) info.videoSources.push(video.src);
                if (video.currentSrc) info.videoSources.push(video.currentSrc);
                
                // Check source tags
                const sources = video.querySelectorAll('source');
                sources.forEach(source => {
                    if (source.src) info.videoSources.push(source.src);
                });
            });
            
            iframes.forEach(iframe => {
                if (iframe.src) info.iframeSources.push(iframe.src);
            });
            
            return info;
        });
        
        console.log('\n' + '='.repeat(60));
        console.log('📊 EXTRACTION RESULTS');
        console.log('='.repeat(60) + '\n');
        
        if (streamUrls.length > 0) {
            console.log('🎯 Stream URLs found:');
            const uniqueUrls = [...new Set(streamUrls)];
            uniqueUrls.forEach((url, index) => {
                console.log(`\n${index + 1}. ${url}`);
            });
        }
        
        if (videoInfo.videoSources.length > 0) {
            console.log('\n🎬 Video element sources:');
            videoInfo.videoSources.forEach((src, index) => {
                console.log(`${index + 1}. ${src}`);
            });
        }
        
        if (videoInfo.iframeSources.length > 0) {
            console.log('\n🖼️  Iframe sources:');
            videoInfo.iframeSources.forEach((src, index) => {
                console.log(`${index + 1}. ${src}`);
            });
        }
        
        if (streamUrls.length === 0 && videoInfo.videoSources.length === 0) {
            console.log('❌ No direct stream URLs found.');
            console.log('\nPossible reasons:');
            console.log('  - Stream uses DRM protection');
            console.log('  - Stream loaded in a separate iframe with CORS');
            console.log('  - Video player uses JavaScript to load the stream');
            console.log('\nTry these alternatives:');
            console.log('  1. Use: yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2"');
            console.log('  2. Check browser DevTools Network tab for .m3u8 files');
            console.log('  3. Use the full page with Chromium (may include ads)');
        }
        
        console.log('\n' + '='.repeat(60) + '\n');
        
    } catch (error) {
        console.error('❌ Error:', error.message);
    } finally {
        await browser.close();
    }
}

// Run the extractor
extractStreamUrl().catch(console.error);

