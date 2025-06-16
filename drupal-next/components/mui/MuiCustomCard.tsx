import { styled, useThemeProps } from '@mui/material/styles'
import { BoxProps } from '@mui/system'
import * as React from 'react'
import { ForwardedRef, HTMLAttributes, ReactElement, ReactNode } from 'react'

// 1. Створюємо окремий тип власних пропсів
interface CustomCardOwnProps {
  headline: string
  cardContent: string | number | ReactElement
  image: string | ReactNode
  variant?: 'outlined' | 'elevation'
}

// 2. Об'єднуємо з BoxProps або HTMLAttributes<HTMLDivElement>
export type CustomCardProps = CustomCardOwnProps &
  HTMLAttributes<HTMLDivElement>

// 3. Передаємо лише потрібні стилям пропси через ownerState
const CustomCardRoot = styled('div', {
  name: 'MuiCustomCard',
  slot: 'Root',
})<{ ownerState: Pick<CustomCardOwnProps, 'variant'> }>(
  ({ theme, ownerState }) => ({
    display: 'flex',
    flexDirection: 'column',
    gap: theme.spacing(0.5),
    backgroundColor: theme.palette.background.paper,
    borderRadius: theme.shape.borderRadius,
    boxShadow: theme.shadows[2],
    letterSpacing: '-0.025em',
    fontWeight: 600,
    ...(ownerState.variant === 'outlined' && {
      border: `2px solid ${theme.palette.divider}`,
      boxShadow: 'none',
      backgroundColor: 'transparent',
    }),
  })
)

// Інші слоти — норм
const CustomCardImage = styled('div', {
  name: 'MuiCustomCard',
  slot: 'Image',
})(() => ({
  width: '100%',
}))

const CustomCardContent = styled('div', {
  name: 'MuiCustomCard',
  slot: 'Content',
})(({ theme }) => ({
  padding: theme.spacing(1),
}))

const CustomCardHeadline = styled('div', {
  name: 'MuiCustomCard',
  slot: 'Headline',
})(({ theme }) => ({
  ...theme.typography.h6,
  color: theme.palette.text.primary,
}))

const CustomCardBody = styled('div', {
  name: 'MuiCustomCard',
  slot: 'Body',
})(({ theme }) => ({
  ...theme.typography.body2,
  color: theme.palette.text.secondary,
}))

// Головний компонент
const CustomCard = React.forwardRef<HTMLDivElement, CustomCardProps>(
  function CustomCard(inProps, ref: ForwardedRef<HTMLDivElement>) {
    const props = useThemeProps({ props: inProps, name: 'MuiCustomCard' })
    const {
      image,
      cardContent,
      headline,
      variant = 'elevation',
      ...other
    } = props

    return (
      <CustomCardRoot ref={ref} ownerState={{ variant }} {...other}>
        <CustomCardImage>{image}</CustomCardImage>
        <CustomCardContent>
          <CustomCardHeadline>{headline}</CustomCardHeadline>
          <CustomCardBody>{cardContent}</CustomCardBody>
        </CustomCardContent>
      </CustomCardRoot>
    )
  }
)

export default CustomCard
