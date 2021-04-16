# Experiments 

## Configuration

Experiments will default to displaying the Control content if no experiment is active (e.g. no `?v=N` value in the URL). If you do not want to show the Control content when no experiment is active, then set the `default` value in your config.php file to null or false.

    $config['experiments'] = [
        'default' => null,
    ];

Other available config options with their default values:

    $config['experiments'] = [
        'queryParameterName' => 'v',
        'queryParameterValue' => null,
        'randomize' => false,
        'default' => 0,
    ];

### Content Tag Pair
   
    {exp:experiments:content choose="{experiment_field_name}"}
        {if control}
           Control Content #1
        {/if}
        
        Always Shown Content
        
        {if control}
           Control Content #2
        {/if}
    
        {if variant_1}
           Variant Content #1
        {/if}

        {if variant_2}
           Variant Content #2
        {/if}

        {if variant_any}
           Variant Content #1, #2, or #3
        {/if}
    {/exp:experiments:content}

#### Tag Parameters

    experiment_id
    query_parameter
    randomize
    choose
    prefix

### Bloqs

Add an Experiments atom to your block and make it a block variable by prefixing the short name with `block_var` [See Bloqs documentation](https://eebloqs.com/documentation/nesting).
This field is designed to work with Bloqs' nestable mode, but will also work with nesting turned off. If you add it to a parent block all child blocks will be hidden or removed based
on the value chosen in the field, and which version of the experiment is being displayed to the end user.

The `{exp:experiments:bloqs}` tag pair is DEPRECATED. You should use the new `{bloqs:children}` tag, and [the Bloqs Experiments extension](https://github.com/litzinger/bloqs-experiments)

![Bloqs Experiment field](addons/experiments/images/bloqs-experiments.png)

### deprecated 

The only updates you need to make to your template  is to wrap the main Bloqs field tag pair with the experiments plugin tag. That's it. Bloqs and the Experiment field will handle the rest.

    {exp:channel:entries channel="pages" entry_id="{segment_2}"}
          {exp:experiments:bloqs}
              {bloqs_field}
                  ... blocks ...
              {/bloqs_field}
          {/exp:experiments:bloqs}
     {/exp:channel:entries}
     

