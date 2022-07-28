import React from "react";

type FrequencyType =  'monthly' | 'yearly';

interface FrequencyProps {

    type: FrequencyType,
    name: string,
    frequency: FrequencyType,
    setFrequency: (frequency: FrequencyType) => void

}

export default function Frequency({type, name, frequency, setFrequency} : FrequencyProps) {
    return <span
        className={type === frequency ? 'active' : ''}
        onClick={() => setFrequency(type)}
    >{name}</span>
}