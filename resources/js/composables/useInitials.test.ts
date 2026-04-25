import { describe, expect, it } from 'vitest';
import { getInitials } from './useInitials';

describe('getInitials', () => {
    it('returns empty string for undefined input', () => {
        expect(getInitials()).toBe('');
    });

    it('returns empty string for empty string', () => {
        expect(getInitials('')).toBe('');
    });

    it('returns first letter uppercased for a single name', () => {
        expect(getInitials('alice')).toBe('A');
    });

    it('returns first and last initials for a full name', () => {
        expect(getInitials('alice cooper')).toBe('AC');
    });

    it('returns first and last initials for three or more names', () => {
        expect(getInitials('alice middle cooper')).toBe('AC');
    });
});
