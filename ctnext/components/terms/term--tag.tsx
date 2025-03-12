import { DrupalTaxonomyTerm } from "next-drupal"

interface DrupalTaxonomyTermProps {
  term: DrupalTaxonomyTerm
}

export function TermTag({ term }: DrupalTaxonomyTerm) {
  return (
    <div className="relative">
      <div className="container inset-0 z-10 flex items-center">
        <div className="top-0 flex flex-col items-start space-y-4 lg:max-w-[40%] text-text px-0 py-6 lg:px-6 lg:text-white lg:border border-text lg:bg-black/40">
          <p className="font-serif text-[28px] leading-tight">
            {term.name}
          </p>
          {term.description && (
            // Not secure way.
            <p className="text-[19px] leading-snug" dangerouslySetInnerHTML={{__html: term.description.value}}></p>
          )}
        </div>
      </div>
    </div>
  )
}
