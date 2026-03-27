// hooks/useResponsiveLayout.js
import { useState, useEffect, useCallback, useMemo } from 'react';

// Breakpoints estándar del sistema
const BREAKPOINTS = {
    xxs: 0,
    xs: 480,
    sm: 768,
    md: 996,
    lg: 1200,
    xl: 1440,
    xxl: 1920
};

// Configuraciones de columnas por breakpoint
const COLUMN_CONFIG = {
    xxs: 1,
    xs: 1,
    sm: 2,
    md: 3,
    lg: 4,
    xl: 5,
    xxl: 6
};

// Configuraciones de espaciado por breakpoint
const SPACING_CONFIG = {
    xxs: { margin: [4, 4], padding: [8, 8] },
    xs: { margin: [6, 6], padding: [12, 12] },
    sm: { margin: [8, 8], padding: [16, 16] },
    md: { margin: [12, 12], padding: [20, 20] },
    lg: { margin: [16, 16], padding: [24, 24] },
    xl: { margin: [20, 20], padding: [32, 32] },
    xxl: { margin: [24, 24], padding: [40, 40] }
};

export const useResponsiveLayout = (customBreakpoints = {}) => {
    const [windowSize, setWindowSize] = useState({
        width: typeof window !== 'undefined' ? window.innerWidth : 1200,
        height: typeof window !== 'undefined' ? window.innerHeight : 800
    });

    const breakpoints = useMemo(() => ({
        ...BREAKPOINTS,
        ...customBreakpoints
    }), [customBreakpoints]);

    // Detectar cambios de tamaño de ventana
    useEffect(() => {
        const handleResize = () => {
            setWindowSize({
                width: window.innerWidth,
                height: window.innerHeight
            });
        };

        // Usar ResizeObserver si está disponible, sino fallback a evento resize
        if (typeof ResizeObserver !== 'undefined') {
            const resizeObserver = new ResizeObserver(entries => {
                for (let entry of entries) {
                    if (entry.target === document.body) {
                        handleResize();
                    }
                }
            });

            resizeObserver.observe(document.body);

            return () => {
                resizeObserver.disconnect();
            };
        } else {
            window.addEventListener('resize', handleResize);
            return () => window.removeEventListener('resize', handleResize);
        }
    }, []);

    // Determinar el breakpoint actual
    const currentBreakpoint = useMemo(() => {
        const width = windowSize.width;
        const sortedBreakpoints = Object.entries(breakpoints)
            .sort(([,a], [,b]) => b - a); // Ordenar de mayor a menor

        for (const [name, minWidth] of sortedBreakpoints) {
            if (width >= minWidth) {
                return name;
            }
        }

        return 'xs';
    }, [windowSize.width, breakpoints]);

    // Propiedades derivadas
    const isMobile = useMemo(() =>
        windowSize.width < breakpoints.sm,
        [windowSize.width, breakpoints.sm]
    );

    const isTablet = useMemo(() =>
        windowSize.width >= breakpoints.sm && windowSize.width < breakpoints.lg,
        [windowSize.width, breakpoints.sm, breakpoints.lg]
    );

    const isDesktop = useMemo(() =>
        windowSize.width >= breakpoints.lg,
        [windowSize.width, breakpoints.lg]
    );

    // Configuración de grid basada en breakpoint
    const gridConfig = useMemo(() => ({
        columns: COLUMN_CONFIG[currentBreakpoint] || 1,
        spacing: SPACING_CONFIG[currentBreakpoint] || SPACING_CONFIG.sm,
        rowHeight: isMobile ? 80 : isTablet ? 100 : 120,
        containerPadding: isMobile ? [8, 8] : [16, 16]
    }), [currentBreakpoint, isMobile, isTablet]);

    // Helper para obtener configuración específica por breakpoint
    const getConfigForBreakpoint = useCallback((config, fallback = null) => {
        if (typeof config === 'object' && !Array.isArray(config)) {
            return config[currentBreakpoint] || config.default || fallback;
        }
        return config || fallback;
    }, [currentBreakpoint]);

    // Helper para aplicar clases CSS responsivas
    const getResponsiveClasses = useCallback((classMap) => {
        const classes = [];

        Object.entries(classMap).forEach(([prefix, value]) => {
            if (currentBreakpoint === prefix || prefix === 'default') {
                if (Array.isArray(value)) {
                    classes.push(...value);
                } else if (value) {
                    classes.push(value);
                }
            }
        });

        return classes.join(' ');
    }, [currentBreakpoint]);

    // Helper para componentes condicionales por breakpoint
    const renderForBreakpoint = useCallback((components) => {
        const priorityOrder = ['xxl', 'xl', 'lg', 'md', 'sm', 'xs', 'xxs'];

        // Buscar el componente para el breakpoint actual o el más cercano menor
        for (const bp of priorityOrder) {
            if (windowSize.width >= breakpoints[bp] && components[bp]) {
                return components[bp];
            }
        }

        return components.default || null;
    }, [windowSize.width, breakpoints]);

    // Configuración de dashboard específica por dispositivo
    const dashboardConfig = useMemo(() => ({
        layout: isMobile ? 'stack' : isTablet ? 'grid-2' : 'grid-flexible',
        sidebar: {
            mode: isMobile ? 'drawer' : isTablet ? 'collapsible' : 'fixed',
            width: isMobile ? '100%' : isTablet ? '300px' : '350px'
        },
        widgets: {
            minWidth: isMobile ? 1 : isTablet ? 1 : 2,
            maxWidth: isMobile ? 1 : 4,
            defaultHeight: isMobile ? 1 : 2,
            allowResize: !isMobile
        },
        filters: {
            position: isMobile ? 'bottom-sheet' : 'sidebar',
            collapsible: isMobile || isTablet
        },
        navigation: {
            type: isMobile ? 'bottom-tabs' : 'top-bar',
            collapsible: isTablet
        }
    }), [isMobile, isTablet]);

    // Media queries programáticas
    const matchesMediaQuery = useCallback((query) => {
        if (typeof window === 'undefined') return false;

        try {
            return window.matchMedia(query).matches;
        } catch (error) {
            console.warn('Error al evaluar media query:', query, error);
            return false;
        }
    }, []);

    // Detección de orientación
    const orientation = useMemo(() => {
        if (typeof window === 'undefined') return 'landscape';

        return windowSize.width > windowSize.height ? 'landscape' : 'portrait';
    }, [windowSize]);

    // Detección de características del dispositivo
    const deviceFeatures = useMemo(() => ({
        hasTouch: typeof window !== 'undefined' && 'ontouchstart' in window,
        hasHover: matchesMediaQuery('(hover: hover)'),
        prefersReducedMotion: matchesMediaQuery('(prefers-reduced-motion: reduce)'),
        highDPI: typeof window !== 'undefined' && window.devicePixelRatio > 1,
        orientation
    }), [matchesMediaQuery, orientation]);

    // Configuración de animaciones basada en capacidades del dispositivo
    const animationConfig = useMemo(() => ({
        enabled: !deviceFeatures.prefersReducedMotion,
        duration: isMobile ? 200 : 300,
        easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
        reduceMotion: deviceFeatures.prefersReducedMotion
    }), [deviceFeatures.prefersReducedMotion, isMobile]);

    return {
        // Información básica de ventana
        windowSize,
        currentBreakpoint,
        breakpoint: currentBreakpoint, // Alias

        // Flags de tipo de dispositivo
        isMobile,
        isTablet,
        isDesktop,

        // Configuraciones específicas
        gridConfig,
        dashboardConfig,
        animationConfig,

        // Características del dispositivo
        deviceFeatures,
        orientation,

        // Helpers
        getConfigForBreakpoint,
        getResponsiveClasses,
        renderForBreakpoint,
        matchesMediaQuery,

        // Breakpoints para referencia
        breakpoints
    };
};