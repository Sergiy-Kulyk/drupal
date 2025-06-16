import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import FormatBoldIcon from '@mui/icons-material/FormatBold';
import FormatItalicIcon from '@mui/icons-material/FormatItalic';
import FormatUnderlineIcon from '@mui/icons-material/FormatUnderlined';
import SendIcon from '@mui/icons-material/Send';
import {
  Button,
  ButtonGroup,
  CollapseProps,
  Container,
  IconButton,
  Stack,
  ToggleButton,
  ToggleButtonGroup,
  type,
} from '@mui/material';
import { Accordion, AccordionDetails, AccordionSummary } from '@mui/material';
import { useState } from 'react';

import { themeOptions } from '../providers/MUIThemeProvider';

function MyWrapper(props) {
  return <section {...props} data-test='custom-root' />
}

const CustomAccordionHeading = (props) => {
  return <span {...props} />
}

const MuiButton = () => {
  const [formats, setFormats] = useState<string[]>([])
  const handleFormatChange = (
    event: React.MouseEvent<HTMLElement>,
    updatedFormats: string[]
  ) => {
    console.log(updatedFormats)
    setFormats[updatedFormats]
  }

  return (
    // A wrapper component that adds spacing.
    <Stack spacing={4} direction={'column'}>
      <Stack spacing={2} direction={'row'}>
        {/* Variants */}
        <Button variant='text'>Text</Button>
        <Button variant='contained'>Contained</Button>
        <Button variant='outlined'>Outlined</Button>
        <Button>Primary</Button>
        <Button disabled>Disabled</Button>
        <Button href='#text-buttons'>Link</Button>
      </Stack>
      <Stack spacing={2} direction={'row'}>
        {/* Colors */}
        <Button variant='contained' color={'primary'}>
          Contained
        </Button>
        <Button variant='contained' color={'secondary'}>
          Contained
        </Button>
        <Button variant='contained' color={'error'}>
          Contained
        </Button>
        <Button variant='contained' color={'success'}>
          Contained
        </Button>
        <Button variant='contained' color={'warning'}>
          Contained
        </Button>
        <Button variant='contained' color={'info'}>
          Contained
        </Button>
        <Button variant='contained' color={'inherit'}>
          Contained
        </Button>
        <Button variant='outlined' color={'primary'}>
          Outlined
        </Button>
        <Button color={'primary'}>Primary</Button>
        <Button disabled color={'primary'}>
          Disabled
        </Button>
        <Button href='#text-buttons' color={'primary'}>
          Link
        </Button>
      </Stack>
      <Stack display={'block'} spacing={2} direction={'row'}>
        {/* Sizes */}
        <Button variant='outlined' size='small' color={'error'}>
          Contained
        </Button>
        <Button variant='text' size='large' color={'error'}>
          Contained
        </Button>
        <Button variant='contained' size='medium' color={'error'}>
          Contained
        </Button>
      </Stack>
      <Stack display={'block'} spacing={2} direction={'row'}>
        {/* Icons */}
        <Button
          variant='outlined'
          startIcon={<SendIcon />}
          endIcon={<SendIcon />}
          color={'error'}
        >
          Contained
        </Button>
        {/* Icon button */}
        <IconButton area-label='send' color='error' size='large'>
          <SendIcon />
        </IconButton>
      </Stack>
      <Stack display={'block'} spacing={2} direction={'row'}>
        {/* Disable some parameters, handle events */}
        <Button
          variant='outlined'
          startIcon={<SendIcon />}
          endIcon={<SendIcon />}
          color={'error'}
          disableElevation
          disableRipple
          onClick={() => alert('click')}
        >
          Contained
        </Button>
      </Stack>
      <Stack display={'block'} spacing={2} direction={'row'}>
        {/* Button groups */}
        <ButtonGroup
          variant='contained'
          orientation='vertical'
          size='large'
          color='success'
          aria-label='button group'
        >
          <Button endIcon={<SendIcon />}>Contained</Button>
          <Button startIcon={<SendIcon />}>outlined</Button>
          <Button>text</Button>
        </ButtonGroup>
      </Stack>
      <Stack display={'block'} spacing={2}>
        {/* Toggle button */}
        <ToggleButtonGroup
          orientation='vertical'
          size='large'
          color='success'
          aria-label='formating group'
          value={formats}
          onChange={handleFormatChange}
        >
          <ToggleButton value={'bold'}>
            <FormatBoldIcon />
          </ToggleButton>
          <ToggleButton value={'italic'}>
            <FormatItalicIcon />
          </ToggleButton>
          <ToggleButton value={'underline'}>
            <FormatUnderlineIcon />
          </ToggleButton>
        </ToggleButtonGroup>
      </Stack>
      <Stack display={'block'} spacing={2} direction={'row'}>
        {/* accordion */}
        <Accordion
          slots={{ root: MyWrapper, heading: Stack }}
          slotProps={{
            root: {
              style: { backgroundColor: 'pink' },
            },
            heading: {
              style: { color: themeOptions.palette?.background.default },
            },
            transition: { timeout: 10000 } as CollapseProps,
          }}
        >
          <AccordionSummary
            expandIcon={<ExpandMoreIcon />}
            aria-controls='panel1-content'
            id='panel1-header'
            slots={{
              content: Stack,
            }}
          >
            Accordion
          </AccordionSummary>
          <AccordionDetails>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse
            malesuada lacus ex, sit amet blandit leo lobortis eget.
          </AccordionDetails>
        </Accordion>
      </Stack>
    </Stack>
  )
}

export default MuiButton
