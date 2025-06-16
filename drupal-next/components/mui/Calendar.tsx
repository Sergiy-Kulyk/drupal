import { AdapterDayjs } from '@mui/x-date-pickers-pro/AdapterDayjs'
import { DateRangeCalendar } from '@mui/x-date-pickers-pro/DateRangeCalendar'
import { LocalizationProvider } from '@mui/x-date-pickers-pro/LocalizationProvider'
import { DemoContainer } from '@mui/x-date-pickers/internals/demo'
import * as React from 'react'

export default function BasicDateRangeCalendar() {
  return (
    <LocalizationProvider dateAdapter={AdapterDayjs}>
      <DemoContainer components={['DateRangeCalendar']}>
        <DateRangeCalendar />
      </DemoContainer>
    </LocalizationProvider>
  )
}
